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
    private EntityManagerInterface $entityManager;
    private $resetPasswordHelper;
    private $client;
    private $request;


    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();
        
        $serializedData = file_get_contents('tests\Controller\request_data_reset_password.txt');
        $requestData = unserialize($serializedData);
        $this->request = new Request($requestData['query'], $requestData['request'], $requestData['attributes'], [], [], $requestData['server']);
        $this->resetPasswordHelper = $this->createMock(ResetPasswordHelperInterface::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        // $this->entityManager = null; // avoid memory leaks
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

    // public function to test the reset function in the reset password controller
    public function testResetPassword()
    {
        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'register@gmail.com']);

        $token = $this->request->attributes->get('token');

        // call the reset function from the reset password controller
        $this->client->request('GET', '/reset-password/reset/'.$token);

        $this->client->followRedirect();

        // fill the form with a new password
        $form = $this->client->getCrawler()->selectButton('Reset password')->form();
        $form['change_password_form[plainPassword][first]'] = 'Password1@';
        $form['change_password_form[plainPassword][second]'] = 'Password1@';
        $this->client->submit($form);
        
        $this->client->followRedirect();

        $this->assertSelectorTextContains('strong', 'Superbe !');
        $this->assertSelectorTextContains('div.alert.alert-success', 'Votre mot de passe a été modifié avec succès.');


    }

   
}
