<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;


class SecurityControllerTest extends WebTestCase
{
    public function testLogoutValid(): void
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

        $client->clickLink('Se déconnecter');
        $client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('a[href="/login"]');
    }
}
