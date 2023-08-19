<?php

namespace App\DataFixtures;

use App\Entity\Task;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{

    private $em;
    private $userPasswordHasher;

    public function __construct(EntityManagerInterface $em, UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->em = $em;
        $this->userPasswordHasher = $userPasswordHasher;
    }
    public function load(ObjectManager $manager): void
    {

        $user = new User();
        $user->setEmail('anonymous@gmail.com');
        $user->setFullname('John Doe');
        $user->setRoles(['ROLE_USER']);

        // create a best practice user password hasher


        // create an encoded password for "password"
        $user->setPassword(
            $this->userPasswordHasher->hashPassword(
                $user,
                'password'
            )
        );

        $user->setPhoto('https://static.wikia.nocookie.net/shadowsdietwice/images/d/d1/Withered_Red_Gourd.png');
        $user->setIsVerified(true);
        $manager->persist($user);

        // Create an admin user
        $adminUser = new User();
        $adminUser->setEmail('adminuser@gmail.com');
        $adminUser->setFullname('Admin Doe');
        $adminUser->setRoles(['ROLE_ADMIN', 'ROLE_USER']);

        // create an encoded password for "passwordadmin"

        $adminUser->setPassword(
            $this->userPasswordHasher->hashPassword(
                $adminUser,
                'passwordadmin'
            )
        );

        $adminUser->setPhoto('https://static.wikia.nocookie.net/shadowsdietwice/images/d/d1/Withered_Red_Gourd.png');
        $adminUser->setIsVerified(true);
        $manager->persist($adminUser);


        // Create an admin user whose admin role will be removed
        $adminUserRoleRemoval = new User();
        $adminUserRoleRemoval->setEmail('adminuserroleremoved@gmail.com');
        $adminUserRoleRemoval->setFullname('Gandalfnotadmin Doe');
        $adminUserRoleRemoval->setRoles(['ROLE_ADMIN', 'ROLE_USER']);

        // create an encoded password for "passwordadmin"

        $adminUserRoleRemoval->setPassword(
            $this->userPasswordHasher->hashPassword(
                $adminUserRoleRemoval,
                'passwordadminremoval'
            )
        );

        $adminUserRoleRemoval->setPhoto('https://static.wikia.nocookie.net/shadowsdietwice/images/d/d1/Withered_Red_Gourd.png');
        $adminUserRoleRemoval->setIsVerified(true);
        $manager->persist($adminUserRoleRemoval);


        // create one task
        $task = new Task(
            'title',
            'content'
        );

        $manager->persist($task);

        $manager->flush();
    }
}
