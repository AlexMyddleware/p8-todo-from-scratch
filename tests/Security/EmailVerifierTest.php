<?php

// class to test the email verifier

namespace App\Tests\Security;

use App\Entity\Task;
use App\Security\EmailVerifier;

use App\Entity\User;
use App\Controller\TaskController;
use App\Repository\TaskRepository;

// use task controller
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

use Symfony\Component\Mailer\MailerInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use Symfony\Component\HttpFoundation\Request;

class EmailVerifierTest extends WebTestCase
{

    private EntityManagerInterface $entityManager;
    private TaskRepository $taskRepository;
    private $client;
    private $request;

    private VerifyEmailHelperInterface $verifyEmailHelper;
    private MailerInterface $mailer;

    protected function setUp(): void
    {

        // create client
        $this->client = static::createClient();

        $this->entityManager = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();
        
        $serializedData = file_get_contents('tests\Security\request_data.txt');
        $requestData = unserialize($serializedData);
        $this->request = new Request($requestData['query'], $requestData['request'], $requestData['attributes'], [], [], $requestData['server']);

        $verifyEmailHelperMock = $this->createMock(VerifyEmailHelperInterface::class);
        $verifyEmailHelperMock->method('validateEmailConfirmation')->willReturnSelf();

        $this->verifyEmailHelper = $verifyEmailHelperMock;
        $this->mailer = $this->client->getContainer()->get(MailerInterface::class);
    }

    public function testEmailVerifier()
    {

        // get the user
        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'testuser@gmail.com']);
        
        // sets the user to not verified
        $user->setIsVerified(false);
        // asserts that the user is not verified
        $this->assertFalse($user->isVerified());

        $emailverifier = new EmailVerifier($this->verifyEmailHelper, $this->mailer, $this->entityManager);

        $emailverifier->handleEmailConfirmation($this->request, $user);

        // refresh the user
        $userVerified = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'testuser@gmail.com']);

            // assert that the user is verified
        $this->assertTrue($userVerified->isVerified());

    }
}
