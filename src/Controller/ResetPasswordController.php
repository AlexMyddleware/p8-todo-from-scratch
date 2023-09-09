<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

abstract class BaseController extends AbstractController
{
    use ResetPasswordControllerTrait;
}

#[Route('/reset-password')]
class ResetPasswordController extends BaseController
{
    use ResetPasswordControllerTrait;

    public function __construct(
        private ResetPasswordHelperInterface $resetPasswordHelper,
        private EntityManagerInterface $entityManager
    ) {
    }

    // public function to set the reset password helper
    public function setResetPasswordHelper(ResetPasswordHelperInterface $resetPasswordHelper) {
        $this->resetPasswordHelper = $resetPasswordHelper;
    }

    // public function to get the reset password helper
    public function getResetPasswordHelper() {
        return $this->resetPasswordHelper;
    }

    protected function proxyGetTokenObjectFromSession() {
        return $this->getTokenObjectFromSession();
    }

    /**
     * Display & process form to request a password reset.
     */
    #[Route('', name: 'app_forgot_password_request', methods: ['GET', 'POST'])]
    public function request(Request $request, MailerInterface $mailer, TranslatorInterface $translator): Response
    {
        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            return $this->processSendingPasswordResetEmail(
                $form->get('email')->getData(),
                $mailer,
                $translator
            );
        }

        return $this->render('reset_password/request.html.twig', [
            'requestForm' => $form->createView(),
        ]);
    }

    /**
     * Confirmation page after a user has requested a password reset.
     */
    #[Route('/check-email', name: 'app_check_email', methods: ['GET', 'POST'])]
    public function checkEmail(): Response
    {
        // Generate a fake token if the user does not exist or someone hit this page directly.
        // This prevents exposing whether or not a user was found with the given email address or not
        if (null === ($resetToken = $this->proxyGetTokenObjectFromSession())) {
            $resetToken = $this->resetPasswordHelper->generateFakeResetToken();
        }

        return $this->render('reset_password/check_email.html.twig', [
            'resetToken' => $resetToken,
        ]);
    }

    /**
    * @codeCoverageIgnore
    */
    protected function getTokenFromSessionWrapper() {
        return $this->getTokenFromSession();
    }
    

    private function handleResetPasswordException(
        ResetPasswordExceptionInterface $e, 
        TranslatorInterface $translator, 
        string $messageKey, 
        string $redirectToRoute
    ): RedirectResponse {
        $this->addFlash(
            'reset_password_error', 
            sprintf(
                '%s - %s',
                $translator->trans($messageKey, [], 'ResetPasswordBundle'),
                $translator->trans($e->getReason(), [], 'ResetPasswordBundle')
            )
        );
    
        return $this->redirectToRoute($redirectToRoute);
    }
    

    /**
     * Validates and process the reset URL that the user clicked in their email.
     */
    #[Route('/reset/{token}', name: 'app_reset_password', methods: ['GET', 'POST'])]
    public function reset(Request $request, UserPasswordHasherInterface $passwordHasher, TranslatorInterface $translator, string $token = null): Response
    {

        if ($token) {
            // We store the token in session and remove it from the URL, to avoid the URL being
            // loaded in a browser and potentially leaking the token to 3rd party JavaScript.
            /**
             * @codeCoverageIgnore
             */
            $this->storeTokenInSession($token);

            return $this->redirectToRoute('app_reset_password');
        }

        $token = $this->getTokenFromSessionWrapper();
        if (null === $token) {
            throw $this->createNotFoundException('No reset password token found in the URL or in the session.');
        }

        try {

            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);

        } catch (ResetPasswordExceptionInterface $e) {
            return $this->handleResetPasswordException(
                $e, 
                $translator, 
                ResetPasswordExceptionInterface::MESSAGE_PROBLEM_VALIDATE, 
                'app_forgot_password_request'
            );
        }

        // The token is valid; allow the user to change their password.
        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // A password reset token should be used only once, remove it.

                $this->resetPasswordHelper->removeResetRequest($token);

            // $this->resetPasswordHelper->removeResetRequest($token);

            // Encode(hash) the plain password, and set it.
            $encodedPassword = $passwordHasher->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            );

            $user->setPassword($encodedPassword);
            $this->entityManager->flush();

            // The session is cleaned up after the password has been changed.
            $this->cleanSessionAfterReset();

            // add flash message
            $this->addFlash('success', 'Votre mot de passe a été modifié avec succès.');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('reset_password/reset.html.twig', [
            'resetForm' => $form->createView(),
        ]);
    }

    public function getUserfromEmail(string $email) {
        return $this->entityManager->getRepository(User::class)->findOneBy([
            'email' => $email,
        ]);
    }

    private function processSendingPasswordResetEmail(string $emailFormData, MailerInterface $mailer, TranslatorInterface $translator): RedirectResponse
    {
        $user = $this->getUserfromEmail($emailFormData);

        // Do not reveal whether a user account was found or not.
        if (!$user) {
            return $this->redirectToRoute('app_check_email');
        }

        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (ResetPasswordExceptionInterface $e) {
            return $this->handleResetPasswordException(
                $e, 
                $translator,
                ResetPasswordExceptionInterface::MESSAGE_PROBLEM_HANDLE, 
                'app_check_email'
            );
        }

        $email = (new TemplatedEmail())
            ->from(new Address('todoadmin@gmail.com', 'Admin Mail Bot'))
            ->to($user->getEmail())
            ->subject('Your password reset request')
            ->htmlTemplate('reset_password/email.html.twig')
            ->context([
                'resetToken' => $resetToken,
            ])
        ;

        $mailer->send($email);

        // Store the token object in session for retrieval in check-email route.
        $this->setTokenObjectInSession($resetToken);

        return $this->redirectToRoute('app_check_email');
    }
}
