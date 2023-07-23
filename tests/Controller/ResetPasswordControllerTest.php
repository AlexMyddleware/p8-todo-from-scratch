<?php
namespace App\Tests\Controller;

use App\Entity\User;
use App\Form\ChangePasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ResetPasswordControllerTest extends WebTestCase
{
    private $entityManager;
    private $resetPasswordHelper;
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->resetPasswordHelper = $this->createMock(ResetPasswordHelperInterface::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager = null; // avoid memory leaks
    }

    public function testResetPasswordRequest()
    {
        $mailerInterface = $this->createMock(MailerInterface::class);
        $translatorInterface = $this->createMock(TranslatorInterface::class);

        $this->client->request('GET', '/reset-password');

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $form = $this->client->getCrawler()->selectButton('Send password reset email')->form();
        $form['reset_password_request_form[email]'] = 'test@example.com';
        $this->client->submit($form);

        // dd($this->client->getResponse()->getContent());

        // $this->assertResponseRedirects('reset-password/check-email');
        // check that the document contains the title Redirecting to /reset-password/check-email
        $this->assertSelectorTextContains('title', 'Redirecting to /reset-password/check-email');

        // follow redirect
        $this->client->followRedirect();

        // dd($this->client->getResponse()->getContent());

        // tests that the response contains the text If an account matching your email exists, then an email was just sent that contains a link that you can use to reset your password.
        
        $this->assertSelectorTextContains('p', 'If an account matching your email exists, then an email was just sent that contains a link that you can use to reset your password.');
    }

   
}
