<?php

namespace App\Tests\Controller;

use App\Entity\User;
use ReflectionMethod;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Form;
use App\Form\ChangePasswordFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Doctrine\Persistence\ObjectRepository;
use App\Controller\ResetPasswordController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;



class ResetPasswordControllerTest extends TestCase
{
    private $resetPasswordHelper;
    private $entityManager;
    private $controller;
    private $mailer;
    private $request;
    private $requestStack;
    private $passwordHasher;
    private $form;
    private $formFactory;
    private $router;
    private $user;
    private $userRepository;
    private $mockTranslator;

    protected function setUp(): void
    {
        // Setting up request
        $this->request = $this->createMock(Request::class);

        $this->requestStack = $this->createMock(RequestStack::class);
        $this->requestStack->method('getCurrentRequest')->willReturn($this->request);

        // mock for password hasher
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->passwordHasher->method('hashPassword')
            ->willReturn('hashed_password');

        // Simulate the form submission
        $this->form = $this->createMock(Form::class);
        $this->form->method('isSubmitted')->willReturn(true);
        $this->form->method('isValid')->willReturn(true);
        $this->form->method('handleRequest')->willReturnSelf();
        $this->form->method('get')->willReturn($this->form);
        $this->form->method('getData')->willReturn('plain_password');

        

        // create mock of MailerInterface 
        $this->mailer = $this->createMock(MailerInterface::class);

        $this->resetPasswordHelper = $this->createMock(ResetPasswordHelperInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->controller = $this->getMockBuilder(ResetPasswordController::class)
            ->setConstructorArgs([$this->resetPasswordHelper, $this->entityManager])
            ->onlyMethods(['getTokenFromSession', 'getTokenFromSessionWrapper', 'addFlash', 'render'])
            ->getMock();

        $this->controller->method('addFlash')->willReturnCallback(function () {
        });

        // Mock FormFactory
        $this->formFactory = $this->createMock(FormFactoryInterface::class);
        $this->formFactory->method('create')->willReturn($this->form);

        // router
        $this->router = $this->createMock(RouterInterface::class);
        $this->router->method('generate')->willReturn('some_fake_url');

        $this->controller->setContainer($this->getContainer([
            'form.factory' => $this->formFactory,
            'request_stack' => $this->requestStack,
            'router' => $this->router,
        ]));

        // user and user repository
        // Mock user entity
        $this->user = $this->createMock(User::class);
        $this->user->method('getEmail')->willReturn('user@example.com');

        // Mocking entity repository to return mocked user
        $this->userRepository = $this->createMock(ObjectRepository::class);
        $this->userRepository->method('findOneBy')->willReturn($this->user);

        // Mock TranslatorInterface
        $this->mockTranslator = $this->createMock(TranslatorInterface::class);
        $this->mockTranslator->method('trans')
            ->willReturn('Some translation');

        $this->entityManager->method('getRepository')
            ->willReturn($this->userRepository);
    }

    public function tokensessionwrapperSetReturn($token)
    {
        $this->controller->method('getTokenFromSessionWrapper')->willReturn($token);
    }

    public function testResetFunction()
    {

        $this->tokensessionwrapperSetReturn('faketokenforregularreset');


        // Mocking ResetPasswordHelper's validateTokenAndFetchUser
        $this->resetPasswordHelper->method('validateTokenAndFetchUser')
            ->willReturn($this->user);

        // We assume that the token session storage was already done
        $token = null;

        $this->user->expects($this->once())
               ->method('setPassword')
               ->with($this->equalTo('hashed_password'));

        $response = $this->controller->reset($this->request, $this->passwordHasher, $this->mockTranslator, $token);


        // Assertions
        $this->assertInstanceOf(RedirectResponse::class, $response);

        $this->assertStringContainsStringIgnoringCase('<title>Redirecting to some_fake_url</title>', $response->getContent());
    }

    public function testResetFunctionInitialRender()
    {

        $this->tokensessionwrapperSetReturn('TokenForInitialRender');

        // Simulate the form submission
        $form = $this->createMock(Form::class);
        $form->method('isSubmitted')->willReturn(false);
        $form->method('isValid')->willReturn(false);
        $form->method('handleRequest')->willReturnSelf();
        $form->method('get')->willReturn($form);
        $form->method('getData')->willReturn('plain_password');

        // Mock FormFactory
        $formFactory = $this->createMock(FormFactoryInterface::class);
        $formFactory->method('create')->willReturn($form);

        $router = $this->createMock(RouterInterface::class);
        $router->method('generate')->willReturn('some_fake_url');

        $this->controller->setContainer($this->getContainer([
            'form.factory' => $formFactory,
            'request_stack' => $this->requestStack,
            'router' => $router,
        ]));

        // Mocking ResetPasswordHelper's validateTokenAndFetchUser
        $this->resetPasswordHelper->method('validateTokenAndFetchUser')
            ->willReturn($this->user);

        // We assume that the token session storage was already done
        $token = null;

        $this->controller->method('render')->willReturn(new Response("test"));

        $response = $this->controller->reset($this->request, $this->passwordHasher, $this->mockTranslator, $token);

        // asserts that the response content contains the string 'test'
        $this->assertStringContainsString('test', $response->getContent());
    }

    public function testErrorValidateTokenAndFetchUser()
    {

        $this->tokensessionwrapperSetReturn('faketokenForErrorValidateTokenAndFetchUser');

        // Mocking ResetPasswordHelper's validateTokenAndFetchUser
        $this->resetPasswordHelper->method('validateTokenAndFetchUser')
            ->willThrowException($this->createMock(ResetPasswordExceptionInterface::class));

        // We assume that the token session storage was already done
        $token = null;

        $response = $this->controller->reset($this->request, $this->passwordHasher, $this->mockTranslator, $token);

        // Assertions
        $this->assertInstanceOf(RedirectResponse::class, $response);

        $this->assertStringContainsStringIgnoringCase('<title>Redirecting to some_fake_url</title>', $response->getContent());
    }

    public function testErrorGenerateResetToken()
    {

        $this->tokensessionwrapperSetReturn('faketokenForErrorGenerateResetToken');

        // Mocking ResetPasswordHelper's validateTokenAndFetchUser
        $this->resetPasswordHelper->method('generateResetToken')
            ->willThrowException($this->createMock(ResetPasswordExceptionInterface::class));

        $emailFormData = 'anonymous@gmail.com';

        // use reflection to call private method processSendingPasswordResetEmail
        $method = new ReflectionMethod(ResetPasswordController::class, 'processSendingPasswordResetEmail');

        $method->setAccessible(true);

        $result = $method->invokeArgs($this->controller, [$emailFormData, $this->mailer, $this->mockTranslator]);

        // Assertions
        $this->assertInstanceOf(RedirectResponse::class, $result);

        $this->assertStringContainsStringIgnoringCase('<title>Redirecting to some_fake_url</title>', $result->getContent());
    }

    public function testRemoveTokenIfPresent()
    {

        $this->tokensessionwrapperSetReturn('faketokenForRemoveTokenIfPresent');

        // Mocking ResetPasswordHelper's validateTokenAndFetchUser
        $this->resetPasswordHelper->method('validateTokenAndFetchUser')
            ->willReturn($this->user);

        // We assume that the token session storage was already done
        $token = 'faketokentest';

        $response = $this->controller->reset($this->request, $this->passwordHasher, $this->mockTranslator, $token);

        // Assertions
        $this->assertInstanceOf(RedirectResponse::class, $response);

        $this->assertStringContainsStringIgnoringCase('<title>Redirecting to some_fake_url</title>', $response->getContent());
    }

    public function testErrorNullTokenFromSessionWrapper()
    {
        // Mocking ResetPasswordHelper's validateTokenAndFetchUser
        $this->resetPasswordHelper->method('validateTokenAndFetchUser')
            ->willReturn($this->user);

        // We assume that the token session storage was already done
        $token = null;

        $this->tokensessionwrapperSetReturn(null);


        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('No reset password token found in the URL or in the session.');

        $this->controller->reset($this->request, $this->passwordHasher, $this->mockTranslator, $token);
    }

    private function getContainer($services): ContainerInterface
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->method('has')->willReturnCallback(function ($id) use ($services) {
            return isset($services[$id]);
        });
        $container->method('get')->willReturnCallback(function ($id) use ($services) {
            return $services[$id];
        });

        return $container;
    }
}
