<?php

namespace App\Tests\DataFixtures;

use App\DataFixtures\AppFixtures;
use App\Entity\Task;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\Persistence\ObjectManager;

class AppFixturesTest extends TestCase
{
    private $entityManagerMock;
    private $userPasswordHasherMock;
    private $appFixtures;

    public function setUp(): void
    {
        // Mock EntityManagerInterface
        /** @var EntityManagerInterface  */
        $this->entityManagerMock = $this->getMockBuilder(EntityManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Mock UserPasswordHasherInterface
        /** @var UserPasswordHasherInterface  */
        $this->userPasswordHasherMock = $this->getMockBuilder(UserPasswordHasherInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Create instance of the fixture with the dependencies injected
        /** @var AppFixtures  */
        $this->appFixtures = new AppFixtures($this->entityManagerMock, $this->userPasswordHasherMock);
    }

    public function tearDown(): void
    {
        unset($this->entityManagerMock);
        unset($this->userPasswordHasherMock);
        unset($this->appFixtures);
    }

    public function testLoad(): void
    {
        // Expect the EntityManagerInterface's persist method to be called twice, once for the user and once for the task
        $this->entityManagerMock->expects($this->exactly(2))
            ->method('persist');

        // Expect the EntityManagerInterface's flush method to be called three times
        $this->entityManagerMock->expects($this->exactly(1))
            ->method('flush');

        $this->userPasswordHasherMock
        ->expects($this->once())
            ->method('hashPassword')
            ->with($this->isInstanceOf(User::class), 'password')
            ->willReturn('hashed_password');


        $this->appFixtures->load($this->entityManagerMock);
    }
}
