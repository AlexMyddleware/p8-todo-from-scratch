<?php

namespace App\Tests\Repository;
use App\Entity\User;
use DateTimeImmutable;
use Symfony\Component\Mime\Email;
use App\Entity\ResetPasswordRequest;
use Doctrine\ORM\EntityManagerInterface;
use App\Controller\ResetPasswordController;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Test\MailerAssertionsTrait;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;



class ResetPasswordRequestRepositoryTest extends WebTestCase
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
    public function testPasswordRecovery(): void
    {
        $crawler = $this->client->request('GET', '/login');

        // click on the password recovery link 'Mot de passe oublié ?'
        $link = $crawler->selectLink('Mot de passe oublié ?')->link();

        // follow the link redirecting to the password recovery page
        $crawler = $this->client->click($link);

        // select the form with the Send password reset email button (in english)
        $form = $crawler->selectButton('Send password reset email')->form([
            'reset_password_request_form[email]' => 'anonymous@gmail.com'
        ]);

        // submit the form
        $this->client->submit($form);

        
        // follow the redirection
        $crawler = $this->client->followRedirect();
        
        // check that the response contains a p with the text "If an account matching your email exists"
        $this->assertSelectorTextContains('p', 'If an account matching your email exists');

    }

    public function testCheckEmailWithNullToken()
    {

        // mock the ResetPasswordHelper
        $resetPasswordHelper = $this->getMockBuilder(ResetPasswordHelperInterface::class)
            ->getMock();

        // get a partial mock of the ResetPasswordController and only mock the getTokenObjectFromSession method
        $resetPasswordController = $this->getMockBuilder(ResetPasswordController::class)
            ->setConstructorArgs([$resetPasswordHelper, $this->entityManager])
            ->onlyMethods(['proxyGetTokenObjectFromSession'])
            ->getMock();
        
        // method getTokenObjectFromSession returns null
        $resetPasswordController->method('proxyGetTokenObjectFromSession')->willReturn(null);

        $crawler = $this->client->request('GET', '/login');

        // click on the password recovery link 'Mot de passe oublié ?'
        $link = $crawler->selectLink('Mot de passe oublié ?')->link();

        // follow the link redirecting to the password recovery page
        $crawler = $this->client->click($link);

        // select the form with the Send password reset email button (in english)
        $form = $crawler->selectButton('Send password reset email')->form([
            'reset_password_request_form[email]' => 'anonymous@gmail.com'
        ]);

        // submit the form
        $this->client->submit($form);

        
        // follow the redirection
        $crawler = $this->client->followRedirect();
    }

    // test the save function
    public function testSave(): void
    {
        // get a user with a valid id from the email
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'anonymous@gmail.com']);

        $datetime  =  new DateTimeImmutable();
        $selector = 'selector';

        $resetPasswordRequest = new ResetPasswordRequest($user, $datetime, $selector, 'hashedToken');

        // get the password request repository
        $resetPasswordRequestRepository = $this->entityManager->getRepository(ResetPasswordRequest::class);

        // save the password request
        $resetPasswordRequestRepository->save($resetPasswordRequest, true);

        // check that the password request has been saved
        $this->assertNotNull($resetPasswordRequest->getId());

    }

    // test the remove function
    public function testRemove(): void
    {
        // get a user with a valid id from the email
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'anonymous@gmail.com']);

        $datetime  =  new DateTimeImmutable();
        $selector = 'selector';

        $resetPasswordRequest = new ResetPasswordRequest($user, $datetime, $selector, 'hashedToken');
        $resetPasswordRequestRepository = $this->entityManager->getRepository(ResetPasswordRequest::class);

        // save the password request
        $resetPasswordRequestRepository->save($resetPasswordRequest, true);

        // find an existing password request
        $resetPasswordRequest = $this->entityManager->getRepository(ResetPasswordRequest::class)->findOneBy(['user' => $user]);

        // get the password request repository

        // remove the password request
        $resetPasswordRequestRepository->remove($resetPasswordRequest, true);

        // check that the password request has been removed
        $this->assertNull($resetPasswordRequest->getId());



    }
    
}
