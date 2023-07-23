<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegistrationControllerTest extends WebTestCase
{
    public function testResponseRegiterPage(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/register');

        $this->assertResponseIsSuccessful();

        $this->assertSelectorTextContains('h1', 'Créer un utilisateur');
        $this->assertSelectorExists('form[name="registration_form"]');
        $this->assertSelectorExists('form[name="registration_form"] input[name="registration_form[fullname]"]');
        $this->assertSelectorExists('form[name="registration_form"] input[name="registration_form[photo]"]');
        $this->assertSelectorExists('form[name="registration_form"] input[name="registration_form[email]"]');
        $this->assertSelectorExists('form[name="registration_form"] input[name="registration_form[agreeTerms]"]');
        $this->assertSelectorExists('form[name="registration_form"] input[name="registration_form[plainPassword]"]');
        $this->assertSelectorExists('form[name="registration_form"] input[name="registration_form[_token]"]');
        $this->assertSelectorExists('form[name="registration_form"] button[type="submit"]');

        $form = $crawler->selectButton('Ajouter')->form([
            'registration_form[fullname]' => 'test',
            'registration_form[photo]' => 'test',
            'registration_form[email]' => 'dudu@gmail.com',
            'registration_form[agreeTerms]' => '1',
            'registration_form[plainPassword]' => 'Password1@',
        ]);

        $client->submit($form);

        if ($client->getResponse()->isRedirection()) {
            $client->followRedirect();
            $this->assertResponseIsSuccessful();
        } else {
            echo 'fail';
            // Form submission might have failed, check response.
            $statusCode = $client->getResponse()->getStatusCode();
            $content = $client->getResponse()->getContent();

            // If status code is not 200 or response content contains error, fail the test
            if ($statusCode != 200 || strpos($content, 'error') !== false) {
                $this->fail("Form submission failed with status code $statusCode and content: $content");
            }
        }
    }
}
