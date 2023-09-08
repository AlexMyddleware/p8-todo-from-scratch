<?php

namespace App\Tests\Repository;

use App\Entity\User;
use DateTimeImmutable;
use Symfony\Component\Mime\Email;
use App\Entity\ResetPasswordRequest;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\ResetPasswordController;
use App\Service\DecoratedResetPasswordHelper;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Test\MailerAssertionsTrait;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;



class DecoratedResetPasswordHelperTest extends WebTestCase
{
    // MailerAssertionsTrait is a trait that provides some useful assertions to test emails
    use MailerAssertionsTrait;

    private EntityManagerInterface $entityManager;
    private $client;

    protected function setUp(): void
    {

        // create client
        $this->client = static::createClient();

        $this->entityManager = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();

        // empty the database after each test
        $this->entityManager->getConnection()->executeStatement('DELETE FROM reset_password_request');

        // reset the auto-increment
        $this->entityManager->getConnection()->executeStatement('ALTER TABLE reset_password_request AUTO_INCREMENT = 1');
    }


    public function testGetResetPasswordHelper()
    {

        // Create a mock for the original ResetPasswordHelperInterface
        $originalHelperMock = $this->createMock(ResetPasswordHelperInterface::class);

        // $decoratedHelper = new DecoratedResetPasswordHelper($originalHelperMock);
        $decoratedHelper = $this->getMockBuilder(DecoratedResetPasswordHelper::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['generateFakeResetToken'])
            ->getMock();


        // Mocking ResetPasswordController
        $resetPasswordController = $this->getMockBuilder(ResetPasswordController::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['proxyGetTokenObjectFromSession', 'render'])
            ->getMock();

        // method getTokenObjectFromSession returns null
        $resetPasswordController->method('proxyGetTokenObjectFromSession')->willReturn(null);

        // method render returns a Response object
        $resetPasswordController->method('render')->willReturn(new Response("test"));

        $resetPasswordController->setResetPasswordHelper($decoratedHelper);

        // assert that getresetpassword helper is an instance of DecoratedResetPasswordHelper
        $this->assertInstanceOf(DecoratedResetPasswordHelper::class, $resetPasswordController->getResetPasswordHelper());
    }
}
