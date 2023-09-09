<?php

namespace App\Tests\Controller;

use App\Entity\User;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Form;
use App\Form\ChangePasswordFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Doctrine\Persistence\ObjectRepository;
use App\Controller\ResetPasswordController;
use Symfony\Component\HttpFoundation\Request;
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

    protected function setUp(): void
    {
        $this->resetPasswordHelper = $this->createMock(ResetPasswordHelperInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->controller = $this->getMockBuilder(ResetPasswordController::class)
        ->setConstructorArgs([$this->resetPasswordHelper, $this->entityManager])
        ->onlyMethods(['getTokenFromSession', 'getTokenFromSessionWrapper', 'addFlash'])
        ->getMock();

        $this->controller->method('addFlash')->willReturnCallback(function() {});

    }

    public function testResetFunction()
    {

        $this->controller->method('getTokenFromSessionWrapper')->willReturn('faketokenforregularreset');


        // Setting up request
        $request = $this->createMock(Request::class);

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn($request);

        // Setting up Password Hasher
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $passwordHasher->method('hashPassword')
            ->willReturn('hashed_password');

        // Simulate the form submission
        $form = $this->createMock(Form::class);
        $form->method('isSubmitted')->willReturn(true);
        $form->method('isValid')->willReturn(true);
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
            'request_stack' => $requestStack,
            'router' => $router,
        ]));

        // Mock user entity
        $user = $this->createMock(User::class);
        $user->method('getEmail')->willReturn('user@example.com');

        // Mocking entity repository to return mocked user
        $userRepository = $this->createMock(ObjectRepository::class);
        $userRepository->method('findOneBy')->willReturn($user);

        // Mock TranslatorInterface
        $mockTranslator = $this->createMock(TranslatorInterface::class);
        $mockTranslator->method('trans')
            ->willReturn('Some translation'); // Return a dummy translation

        $this->entityManager->method('getRepository')
            ->willReturn($userRepository);

        // Mocking ResetPasswordHelper's validateTokenAndFetchUser
        $this->resetPasswordHelper->method('validateTokenAndFetchUser')
            ->willReturn($user);

        // We assume that the token session storage was already done
        $token = null;

        $response = $this->controller->reset($request, $passwordHasher, $mockTranslator, $token);

        // Assertions
        $this->assertInstanceOf(RedirectResponse::class, $response);

        $this->assertStringContainsStringIgnoringCase('<title>Redirecting to some_fake_url</title>', $response->getContent());
    }

    public function testErrorValidateTokenAndFetchUser()
    {

        $this->controller->method('getTokenFromSessionWrapper')->willReturn('faketokenforregularreset');


        // Setting up request
        $request = $this->createMock(Request::class);

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn($request);

        // Setting up Password Hasher
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $passwordHasher->method('hashPassword')
            ->willReturn('hashed_password');

        // Simulate the form submission
        $form = $this->createMock(Form::class);
        $form->method('isSubmitted')->willReturn(true);
        $form->method('isValid')->willReturn(true);
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
            'request_stack' => $requestStack,
            'router' => $router,
        ]));

        // Mock user entity
        $user = $this->createMock(User::class);
        $user->method('getEmail')->willReturn('user@example.com');

        // Mocking entity repository to return mocked user
        $userRepository = $this->createMock(ObjectRepository::class);
        $userRepository->method('findOneBy')->willReturn($user);

        // Mock TranslatorInterface
        $mockTranslator = $this->createMock(TranslatorInterface::class);
        $mockTranslator->method('trans')
            ->willReturn('Some translation'); // Return a dummy translation

        $this->entityManager->method('getRepository')
            ->willReturn($userRepository);

        // Mocking ResetPasswordHelper's validateTokenAndFetchUser
        $this->resetPasswordHelper->method('validateTokenAndFetchUser')
        ->willThrowException($this->createMock(ResetPasswordExceptionInterface::class));

        // We assume that the token session storage was already done
        $token = null;

        $response = $this->controller->reset($request, $passwordHasher, $mockTranslator, $token);

        // Assertions
        $this->assertInstanceOf(RedirectResponse::class, $response);

        $this->assertStringContainsStringIgnoringCase('<title>Redirecting to some_fake_url</title>', $response->getContent());
    }

    public function testRemoveTokenIfPresent()
    {

        $this->controller->method('getTokenFromSessionWrapper')->willReturn('faketokenforremovetokentest');

        // Setting up request
        $request = $this->createMock(Request::class);

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn($request);

        // Setting up Password Hasher
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $passwordHasher->method('hashPassword')
            ->willReturn('hashed_password');

        // Simulate the form submission
        $form = $this->createMock(Form::class);
        $form->method('isSubmitted')->willReturn(true);
        $form->method('isValid')->willReturn(true);
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
            'request_stack' => $requestStack,
            'router' => $router,
        ]));

        // Mock user entity
        $user = $this->createMock(User::class);
        $user->method('getEmail')->willReturn('user@example.com');

        // Mocking entity repository to return mocked user
        $userRepository = $this->createMock(ObjectRepository::class);
        $userRepository->method('findOneBy')->willReturn($user);

        // Mock TranslatorInterface
        $mockTranslator = $this->createMock(TranslatorInterface::class);
        $mockTranslator->method('trans')
            ->willReturn('Some translation'); // Return a dummy translation

        $this->entityManager->method('getRepository')
            ->willReturn($userRepository);

        // Mocking ResetPasswordHelper's validateTokenAndFetchUser
        $this->resetPasswordHelper->method('validateTokenAndFetchUser')
            ->willReturn($user);

        // We assume that the token session storage was already done
        $token = 'faketokentest';

        $response = $this->controller->reset($request, $passwordHasher, $mockTranslator, $token);

        // Assertions
        $this->assertInstanceOf(RedirectResponse::class, $response);

        $this->assertStringContainsStringIgnoringCase('<title>Redirecting to some_fake_url</title>', $response->getContent());
    }

    public function testErrorNullTokenFromSessionWrapper()
    {
        // Setting up request
        $request = $this->createMock(Request::class);

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn($request);

        // Setting up Password Hasher
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $passwordHasher->method('hashPassword')
            ->willReturn('hashed_password');

        // Simulate the form submission
        $form = $this->createMock(Form::class);
        $form->method('isSubmitted')->willReturn(true);
        $form->method('isValid')->willReturn(true);
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
            'request_stack' => $requestStack,
            'router' => $router,
        ]));

        // Mock user entity
        $user = $this->createMock(User::class);
        $user->method('getEmail')->willReturn('user@example.com');

        // Mocking entity repository to return mocked user
        $userRepository = $this->createMock(ObjectRepository::class);
        $userRepository->method('findOneBy')->willReturn($user);

        // Mock TranslatorInterface
        $mockTranslator = $this->createMock(TranslatorInterface::class);
        $mockTranslator->method('trans')
            ->willReturn('Some translation'); // Return a dummy translation

        $this->entityManager->method('getRepository')
            ->willReturn($userRepository);

        // Mocking ResetPasswordHelper's validateTokenAndFetchUser
        $this->resetPasswordHelper->method('validateTokenAndFetchUser')
            ->willReturn($user);

        // We assume that the token session storage was already done
        $token = null;

        $this->controller->method('getTokenFromSessionWrapper')->willReturn(null);


        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('No reset password token found in the URL or in the session.');
        
        $this->controller->reset($request, $passwordHasher, $mockTranslator, $token);

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
