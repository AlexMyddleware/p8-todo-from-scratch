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
        // Expect the EntityManagerInterface's persist method to be called four times
        $this->entityManagerMock->expects($this->exactly(8))
            ->method('persist');

        // Expect the EntityManagerInterface's flush method to be called three times
        $this->entityManagerMock->expects($this->exactly(2))
            ->method('flush');

            $this->userPasswordHasherMock
            ->expects($this->exactly(5)) // Expect 5 calls to hashPassword method
            ->method('hashPassword')
            ->withConsecutive(
                [$this->isInstanceOf(User::class), 'password'], // First call with these parameters
                [$this->isInstanceOf(User::class), 'passwordadmin'], // Second call with these parameters
                [$this->isInstanceOf(User::class), 'passwordadminremoval'], // third call with these parameters
                [$this->isInstanceOf(User::class), 'passwordregister'], // third call with these parameters
                [$this->isInstanceOf(User::class), 'passwordnormal'] // third call with these parameters
                

            )
            ->willReturnOnConsecutiveCalls('hashed_password', 'hashed_password_admin', 'hashed_password_admin_removal', 'hashed_password_register', 'hashed_password_normal');

        $this->appFixtures->load($this->entityManagerMock);
    }
}
