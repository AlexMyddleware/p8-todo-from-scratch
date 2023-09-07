<?php

namespace App\Tests;

use App\Entity\User;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\RegistrationController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class RegistrationControllerTest extends WebTestCase
{
    private EntityManagerInterface $entityManager;
    private $client;
    private $request;
    private $registrationController;
    private EmailVerifier $emailVerifier;

    private VerifyEmailHelperInterface $verifyEmailHelper;
    private MailerInterface $mailer;

    protected function setUp(): void
    {

        // create client
        $this->client = static::createClient();

        $this->entityManager = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();
        
        $this->request = $this->createMock(Request::class);

        $verifyEmailHelperMock = $this->createMock(VerifyEmailHelperInterface::class);
        $verifyEmailHelperMock->method('validateEmailConfirmation')->willReturnSelf();

        $this->verifyEmailHelper = $verifyEmailHelperMock;
        $this->mailer = $this->client->getContainer()->get(MailerInterface::class);


        $this->emailVerifier = new EmailVerifier($this->verifyEmailHelper, $this->mailer, $this->entityManager);

        $this->registrationController = $this->createPartialMock(
            RegistrationController::class,
            ['getUser', 'addFlash', 'redirectToRoute']
        );

        $this->registrationController->method('addFlash')->willReturnCallback(function() {});
        // redirect to route will return to the homepage in the form of a RedirectResponse
        $this->registrationController->method('redirectToRoute')->willReturn(new RedirectResponse('/'));

        // Use reflection to change the accessibility of $emailVerifier property
        $reflection = new \ReflectionClass(RegistrationController::class);
        $property = $reflection->getProperty('emailVerifier');
        $property->setAccessible(true);
        $property->setValue($this->registrationController, $this->emailVerifier);
        
    }

    
    public function testResponseRegiterPage(): void
    {

        $crawler = $this->client->request('GET', '/register');

        $this->assertResponseIsSuccessful();

        $this->assertSelectorTextContains('h1', 'Créer un utilisateur');
        $this->assertSelectorExists('form[name="registration_form"]');
        $this->assertSelectorExists('form[name="registration_form"] input[name="registration_form[fullname]"]');
        $this->assertSelectorExists('form[name="registration_form"] input[name="registration_form[photo]"]');
        $this->assertSelectorExists('form[name="registration_form"] input[name="registration_form[email]"]');
        $this->assertSelectorExists('form[name="registration_form"] input[name="registration_form[agreeTerms]"]');
        $this->assertSelectorExists('form[name="registration_form"] input[name="registration_form[plainPassword]"]');
        $this->assertSelectorExists('form[name="registration_form"] input[name="registration_form[_token]"]');
        $this->assertSelectorExists('form[name="registration_form"] button[type="submit"]');

        $form = $crawler->selectButton('Ajouter')->form([
            'registration_form[fullname]' => 'test',
            'registration_form[photo]' => 'test',
            'registration_form[email]' => 'dudu@gmail.com',
            'registration_form[agreeTerms]' => '1',
            'registration_form[plainPassword]' => 'Password1@',
        ]);

        $this->client->submit($form);

        if ($this->client->getResponse()->isRedirection()) {
            $this->client->followRedirect();
            $this->assertResponseIsSuccessful();
        } else {
            echo 'fail';
            // Form submission might have failed, check response.
            $statusCode = $this->client->getResponse()->getStatusCode();
            $content = $this->client->getResponse()->getContent();

            // If status code is not 200 or response content contains error, fail the test
            if ($statusCode != 200 || strpos($content, 'error') !== false) {
                $this->fail("Form submission failed with status code $statusCode and content: $content");
            }
        }
    }

    // function to test the verifyUserEmail function in the registration controller
    public function testVerifyUserEmail(): void
    {
        // get the user
        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'register@gmail.com']);

        $this->registrationController->method('getUser')->willReturn($user);

        // sets the user to not verified
        $user->setIsVerified(false);

        $this->assertFalse($user->isVerified());

        // create a mock of the translator
        $translator = $this->createMock(TranslatorInterface::class);

        $this->registrationController->verifyUserEmail($this->request, $translator);

        // refresh the user
        $userVerified = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'register@gmail.com']);

        

            // assert that the user is verified
        $this->assertTrue($userVerified->isVerified());
    }
    // function to test the verifyUserEmail function in the registration controller
    public function testVerifyUserEmailNotLoggedIn(): void
    {
        // get the user
        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'register@gmail.com']);


        // sets the user to not verified
        $user->setIsVerified(false);

        $this->assertFalse($user->isVerified());

        // create a mock of the translator
        $translator = $this->createMock(TranslatorInterface::class);

        $this->registrationController->method('getUser')->willReturn(null);
        
        $this->registrationController->method('addFlash')->willReturnCallback(function() {});
        $this->registrationController->method('redirectToRoute')->willReturnCallback(function() {});
        $response = $this->registrationController->verifyUserEmail($this->request, $translator);
        $this->assertEquals('/', $response->headers->get('location'));
        
    }

    // function to test the user set password function
    public function testSetPassword(): void
    {
        // get the user
        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'anonymous@gmail.com']);
        
        // get the current password of the user
        $currentPassword = $user->getPassword();

        // get mock of the password hasher
        $userPasswordHasher = $this->createMock(UserPasswordHasherInterface::class);

        // set the password to a new password
        $user->setPassword(
            
                                $userPasswordHasher->hashPassword(
                                    $user,
                                    "newpassword"
                                )
                            );


        // refresh the user
        $userRefreshed = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'anonymous@gmail.com']);
        
        // assert that the password has changed
        $this->assertNotEquals($currentPassword, $userRefreshed->getPassword());
        
    }
}
