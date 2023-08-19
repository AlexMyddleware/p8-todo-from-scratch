<?php

namespace App\Tests\Repository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Test\MailerAssertionsTrait;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;



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
    
}
