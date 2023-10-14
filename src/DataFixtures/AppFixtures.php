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

        // create a normal user with the email register@gmail.com
        $normalUser = new User();
        $normalUser->setEmail('register@gmail.com');
        $normalUser->setFullname('Register Doe');
        $normalUser->setRoles(['ROLE_USER']);
        
        // create an encoded password for "passwordregister"
        $normalUser->setPassword(
            $this->userPasswordHasher->hashPassword(
                $normalUser,
                'passwordregister'
            )
        );

        // set photo
        $normalUser->setPhoto('https://static.wikia.nocookie.net/shadowsdietwice/images/d/d1/Withered_Red_Gourd.png');

        // set verified to false
        $normalUser->setIsVerified(false);
        // persist
        $manager->persist($normalUser);

        // create a normal user with the email normaluser@gmailcom

        $normalUser2 = new User();
        
        $normalUser2->setEmail('normaluser@gmailcom');

        $normalUser2->setFullname('Normal Doe');

        $normalUser2->setRoles(['ROLE_USER']);

        // create an encoded password for "passwordnormal"
        $normalUser2->setPassword(
            $this->userPasswordHasher->hashPassword(
                $normalUser2,
                'passwordnormal'
            )
        );

        // set photo
        $normalUser2->setPhoto('https://static.wikia.nocookie.net/shadowsdietwice/images/d/d1/Withered_Red_Gourd.png');

        // set verified to true
        $normalUser2->setIsVerified(true);

        // persist
        $manager->persist($normalUser2);


        // create one task
        $task = new Task(
            'title',
            'content'
        );

        $manager->persist($task);

        // create one task with the author being adminuserroleremoved@gmail.com
        $task2 = new Task(
            'Task normal user',
            'content2, task can be deleted by normaluser'
        );

        
        $manager->persist($task2);
        
        $manager->flush();
        $task2->setCreatedBy($normalUser2);

        // create task 3 that should be deleted by adminuser
        $task3 = new Task(
            'Task admin user',
            'content3, task can be deleted by adminuser'
        );

        $task3->setCreatedBy($adminUser);

        $manager->persist($task3);

        // flush
        $manager->flush();
    }
}
