<?php
namespace App\Tests\Repository;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

class UserRepositoryTest extends WebTestCase
{
    private $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

            $this->entityManager->getConnection()->executeStatement('DELETE FROM user where email = "testRemove@gmail.com"');
            $this->entityManager->getConnection()->executeStatement('DELETE FROM user where email = "testUpgrade@gmail.com"');
            // reset the auto-increment
            $this->entityManager->getConnection()->executeStatement('ALTER TABLE user AUTO_INCREMENT = 1');
    }

    public function testRemoveUser()
    {
        $user = new User();
        $user->setEmail('testRemove@gmail.com');
        $user->setPassword('testPassword');
        $user->setFullname('Test User');
        $user->setRoles(['ROLE_USER']);
        $user->setIsVerified(true);
        $user->setPhoto('testPhoto');

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->assertNotNull($this->entityManager->getRepository(User::class)->findOneByEmail('testRemove@gmail.com'));

        $this->entityManager->getRepository(User::class)->remove($user, true);

        $this->assertNull($this->entityManager->getRepository(User::class)->findOneByEmail('testRemove@gmail.com'));
    }

    public function testUpgradePassword()
    {
        $user = new User();
        $user->setEmail('testUpgrade@gmail.com');
        $user->setPassword('oldPassword');
        $user->setFullname('Test User');
        $user->setRoles(['ROLE_USER']);
        $user->setIsVerified(true);
        $user->setPhoto('testPhoto');

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->entityManager->getRepository(User::class)->upgradePassword($user, 'newHashedPassword');

        $upgradedUser = $this->entityManager->getRepository(User::class)->findOneByEmail('testUpgrade@gmail.com');
        $this->assertEquals('newHashedPassword', $upgradedUser->getPassword());
    }

    public function testUpgradePasswordWithUnsupportedUser()
    {
        $this->expectException(UnsupportedUserException::class);

        $unsupportedUser = $this->createMock(PasswordAuthenticatedUserInterface::class);
        $this->entityManager->getRepository(User::class)->upgradePassword($unsupportedUser, 'newHashedPassword');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager->close();
        $this->entityManager = null;
    }
}
