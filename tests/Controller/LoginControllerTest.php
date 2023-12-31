<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LoginControllerTest extends WebTestCase
{
    public function testLoginPageIsUp()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form[action="/login"]');
        $this->assertSelectorExists('input#username[name="_username"]');
        $this->assertSelectorExists('input#password[name="_password"]');
    }

    public function testLoginWithGoodCredentials()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => 'anonymous@gmail.com',
            '_password' => 'password',
        ]);

        $client->submit($form);
        
        $this->assertTrue($client->getResponse()->isRedirect());
        $client->followRedirect();
        $this->assertResponseIsSuccessful();
        // test that the site contains the logout button
        $this->assertSelectorExists('a[href="/logout"]');
    }

    // test with  bad credentials
    public function testLoginWithBadCredentials()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/login');

        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => 'choupacabra',
            '_password' => 'password',
        ]);

        $client->submit($form);

        // follow redirect
        $client->followRedirect();

        $this->assertResponseIsSuccessful();

        $this->assertSelectorExists('.alert.alert-danger');
        // assert that alert contains the word "Identifiants invalides."
        $this->assertSelectorTextContains('.alert.alert-danger', 'Identifiants invalides.');
    }
}
