<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\Trick;
use DateTimeImmutable;
use App\Entity\ImageLink;
use App\Entity\VideoLink;
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Bundle\FixturesBundle\Fixture;

class AppFixtures extends Fixture
{

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    public function load(ObjectManager $manager): void
    {

        $user = new User();
        $user->setEmail('example@example.com');
        $user->setFullname('John Doe');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword('password');
        $user->setPhoto('https://static.wikia.nocookie.net/shadowsdietwice/images/d/d1/Withered_Red_Gourd.png');
        $user->setIsVerified(true);
        $manager->persist($user);
        $manager->flush();

        $manager->flush();
    }
}
