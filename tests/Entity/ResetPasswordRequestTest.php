<?php

namespace App\Tests\Entity;

use App\Entity\User;
use DateTimeImmutable;
use App\Repository\UserRepository;
use App\Entity\ResetPasswordRequest;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ResetPasswordRequestTest extends WebTestCase
{
    // get entity manager
    private $entityManager;
    private UserRepository $userRepository;
    private $client;

    public function setUp(): void
    {

        parent::setUp();

        // create client
        $this->client = static::createClient();

        $this->entityManager = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->userRepository = $this->entityManager
            ->getRepository(User::class);
    }


    // public function to test the getId(): function
    public function testGetId()
    {

        // get a user with a valid id from the email aleronloche@gmail.com

        $datetime  =  new DateTimeImmutable();

        $user = $this->userRepository->findOneBy(['email' => 'anonymous@gmail.com']);

        $selector = 'selector';

        $resetPasswordRequest = new ResetPasswordRequest($user, $datetime, $selector, 'hashedToken');

        // save it 
        $this->entityManager->persist($resetPasswordRequest);
        $this->entityManager->flush();
        $passwordRequestId = $resetPasswordRequest->getId();

        // assert the passwordRequestId is a valid integer
        $this->assertIsInt($passwordRequestId);
    }

    // public function to test the getUser(): function
    public function testGetUser()
    {

        // get a user with a valid id from the email
        $user = $this->userRepository->findOneBy(['email' => 'anonymous@gmail.com']);

        $datetime  =  new DateTimeImmutable();

        $selector = 'selector';

        $resetPasswordRequest = new ResetPasswordRequest($user, $datetime, $selector, 'hashedToken');

        // save it 
        $this->entityManager->persist($resetPasswordRequest);
        $this->entityManager->flush();

        // get the user of te resetPasswordRequest
        $userOfRequest = $resetPasswordRequest->getUser();

        // assert the user of the resetPasswordRequest is the same as the user we got from the email
        $this->assertSame($user, $userOfRequest);
    }
}
