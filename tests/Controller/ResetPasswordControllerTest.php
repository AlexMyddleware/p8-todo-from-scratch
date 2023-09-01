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
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
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
        ->onlyMethods(['getTokenFromSession', 'getTokenFromSessionWrapper'])
        ->getMock();

        $this->controller->method('getTokenFromSessionWrapper')->willReturn('faketoken');
    }

    public function testResetFunction()
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

        $this->controller->setContainer($this->getContainer([
            'form.factory' => $formFactory,
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
        // You can add more assertions based on what you expect
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
