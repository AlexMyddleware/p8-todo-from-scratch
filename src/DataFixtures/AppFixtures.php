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


        $trick = new Trick();
        $trick->setName('Ollie');
        $email = 'example@example.com';
        $userRepository = $this->em->getRepository(User::class);
        $user = $userRepository->findOneBy(['email' => $email]);

        if (!$user) {
            throw new \Exception(sprintf('User with email "%s" not found', $email));
        }

        $trick->setCreator($user);
        $trick->setDescription('The easiest trick');
        $trick->setTrickgroup('Beginner');
        $imageLink = new ImageLink();
        $imageLink->setContent('https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS8IhoEpCH_ayhQAJsa93Nf74sijMagmdZ_Axjz36wh1N-O-FtQ9-B0mNwOC7YLg-g6Qcw&usqp=CAU');
        $trick->addImageLink($imageLink);

        $imageLink2 = new ImageLink();
        $imageLink2->setContent('https://assets2.rockpapershotgun.com/obv.jpg/BROK/resize/1920x1920%3E/format/jpg/quality/80/obv.jpg');
        $trick->addImageLink($imageLink2);

        $videoLink = new VideoLink();
        $videoLink->setContent('https://www.youtube.com/watch?v=kOyCsY4rBH0');
        
        $trick->addVideoLink($videoLink);

        $videoLink2 = new VideoLink();
        $videoLink2->setContent('https://www.youtube.com/watch?v=ZsNPUaYV4PM');
        $trick->addVideoLink($videoLink2);

        $trick->setCreatedAt(new DateTimeImmutable());
        $trick->setmodifiedAt(new DateTimeImmutable());
        $trick->setDeleted(0);
        $trick->setDiscussionChannel('empty');
        
        $manager->persist($trick);



        $trickdos = new Trick();
        $trickdos->setName('Melon');
        $trickdos->setCreator($user);
        $trickdos->setDescription('Grab Your board in the air !');
        $trickdos->setTrickgroup('Beginner');

        $imageLink = new ImageLink();
        $imageLink->setContent('https://cdn.shopify.com/s/files/1/0230/2239/articles/Basic-Grabs-On-A-Snowboard.jpg?v=1517794316');
        $trickdos->addImageLink($imageLink);
        $imageLink2 = new ImageLink();
        $imageLink2->setContent('https://assets2.rockpapershotgun.com/obv.jpg/BROK/resize/1920x1920%3E/format/jpg/quality/80/obv.jpg');
        $trickdos->addImageLink($imageLink2);
        $videoLink = new VideoLink();
        $videoLink->setContent('https://www.youtube.com/watch?v=kOyCsY4rBH0');
        $trickdos->addVideoLink($videoLink);
        $videoLink2 = new VideoLink();
        $videoLink2->setContent('https://www.youtube.com/watch?v=ZsNPUaYV4PM');
        $trickdos->addVideoLink($videoLink2);


        $trickdos->setCreatedAt(new DateTimeImmutable());
        $trickdos->setmodifiedAt(new DateTimeImmutable());
        $trickdos->setDeleted(0);
        $trickdos->setDiscussionChannel('empty');
        
        $manager->persist($trickdos);


        $tricktres = new Trick();
        $tricktres->setName('Relay');
        $tricktres->setCreator($user);
        $tricktres->setDescription('example three !');
        $tricktres->setTrickgroup('Beginner');
        $imageLink = new ImageLink();
        $imageLink->setContent('https://s.yimg.com/ny/api/res/1.2/f9Q1Y.TCm6rI..kPUX9b5g--/YXBwaWQ9aGlnaGxhbmRlcjt3PTk2MDtoPTY1MTtjZj13ZWJw/https://media.zenfs.com/en/the_independent_635/52a869d4a4fb4b874880012e765aef70');
        $tricktres->addImageLink($imageLink);
        $imageLink2 = new ImageLink();
        $imageLink2->setContent('https://assets2.rockpapershotgun.com/obv.jpg/BROK/resize/1920x1920%3E/format/jpg/quality/80/obv.jpg');
        $tricktres->addImageLink($imageLink2);
        $videoLink = new VideoLink();
        $videoLink->setContent('https://www.youtube.com/watch?v=kOyCsY4rBH0');
        $tricktres->addVideoLink($videoLink);
        $videoLink2 = new VideoLink();
        $videoLink2->setContent('https://www.youtube.com/watch?v=ZsNPUaYV4PM');
        $tricktres->addVideoLink($videoLink2);
        $tricktres->setCreatedAt(new DateTimeImmutable());
        $tricktres->setmodifiedAt(new DateTimeImmutable());
        $tricktres->setDeleted(0);
        $tricktres->setDiscussionChannel('empty');
        
        $manager->persist($tricktres);


        $tricktroispointcinq = new Trick();
        $tricktroispointcinq->setName('Jack');
        $tricktroispointcinq->setCreator($user);
        $tricktroispointcinq->setDescription('example four !');
        $tricktroispointcinq->setTrickgroup('Medium');
        
        $imageLink = new ImageLink();
        $imageLink->setContent('https://ridestoremagazine.imgix.net/http%3A%2F%2Fwordpress-604950-1959020.cloudwaysapps.com%2Fwp-content%2Fuploads%2F2022%2F04%2Fjoh01043-v2.jpg?ixlib=gatsbySourceUrl-1.6.9&auto=format%2Ccompress&w=3200&h=4000&s=6a41975ecfcf6bc6e10f20d84597bebe');
        $tricktroispointcinq->addImageLink($imageLink);
        $imageLink2 = new ImageLink();
        $imageLink2->setContent('https://assets2.rockpapershotgun.com/obv.jpg/BROK/resize/1920x1920%3E/format/jpg/quality/80/obv.jpg');
        $tricktroispointcinq->addImageLink($imageLink2);
        $videoLink = new VideoLink();
        $videoLink->setContent('https://www.youtube.com/watch?v=kOyCsY4rBH0');
        $tricktroispointcinq->addVideoLink($videoLink);
        $videoLink2 = new VideoLink();
        $videoLink2->setContent('https://www.youtube.com/watch?v=ZsNPUaYV4PM');
        $tricktroispointcinq->addVideoLink($videoLink2);
        
        $tricktroispointcinq->setCreatedAt(new DateTimeImmutable());
        $tricktroispointcinq->setmodifiedAt(new DateTimeImmutable());
        $tricktroispointcinq->setDeleted(0);
        $tricktroispointcinq->setDiscussionChannel('empty');
        
        $manager->persist($tricktroispointcinq);


        $trickfour = new Trick();
        $trickfour->setName('Stalefish');
        $trickfour->setCreator($user);
        $trickfour->setDescription('Stalefish is a trickfour that involves grabbing the heel edge of the board with the rear hand while the front leg is extended straight out.');
        $trickfour->setTrickgroup('Intermediate');
        $imageLink = new ImageLink();
        $imageLink->setContent('https://cdn.shopify.com/s/files/1/0230/2239/files/Canadian_Bacon_Tim_Eddy_side_Fenelon_large.jpg?1819627745986436554');
        $trickfour->addImageLink($imageLink);
        $imageLink2 = new ImageLink();
        $imageLink2->setContent('https://assets2.rockpapershotgun.com/obv.jpg/BROK/resize/1920x1920%3E/format/jpg/quality/80/obv.jpg');
        $trickfour->addImageLink($imageLink2);
        $videoLink = new VideoLink();
        $videoLink->setContent('https://www.youtube.com/watch?v=kOyCsY4rBH0');
        $trickfour->addVideoLink($videoLink);
        $videoLink2 = new VideoLink();
        $videoLink2->setContent('https://www.youtube.com/watch?v=ZsNPUaYV4PM');
        $trickfour->addVideoLink($videoLink2);
        $trickfour->setCreatedAt(new DateTimeImmutable());
        $trickfour->setmodifiedAt(new DateTimeImmutable());
        $trickfour->setDeleted(0);
        $trickfour->setDiscussionChannel('empty');

        $manager->persist($trickfour);


        $trickfive = new Trick();
        $trickfive->setName('Tail Grab');
        $trickfive->setCreator($user);
        $trickfive->setDescription('Tail Grab is a trick that involves grabbing the tail of the snowboard with the rear hand.');
        $trickfive->setTrickgroup('Beginner');

        $imageLink = new ImageLink();
        $imageLink->setContent('https://ridestoremagazine.imgix.net/http%3A%2F%2Fwordpress-604950-1959020.cloudwaysapps.com%2Fwp-content%2Fuploads%2F2022%2F04%2Fjoh01043-v2.jpg?ixlib=gatsbySourceUrl-1.6.9&auto=format%2Ccompress&w=689&h=861&s=5b283f8575471fa62b738080a2812006');
        $trickfive->addImageLink($imageLink);
        $imageLink2 = new ImageLink();
        $imageLink2->setContent('https://assets2.rockpapershotgun.com/obv.jpg/BROK/resize/1920x1920%3E/format/jpg/quality/80/obv.jpg');
        $trickfive->addImageLink($imageLink2);
        $videoLink = new VideoLink();
        $videoLink->setContent('https://www.youtube.com/watch?v=kOyCsY4rBH0');
        $trickfive->addVideoLink($videoLink);
        $videoLink2 = new VideoLink();
        $videoLink2->setContent('https://www.youtube.com/watch?v=ZsNPUaYV4PM');
        $trickfive->addVideoLink($videoLink2);

        $trickfive->setCreatedAt(new DateTimeImmutable());
        $trickfive->setmodifiedAt(new DateTimeImmutable());
        $trickfive->setDeleted(0);
        $trickfive->setDiscussionChannel('empty');

        $manager->persist($trickfive);



        $tricksix = new Trick();
        $tricksix->setName('Method Grab');
        $tricksix->setCreator($user);
        $tricksix->setDescription('Method Grab is a trick that involves grabbing the heel edge of the board with the rear hand while the front leg is bent and the rear leg is extended.');
        $tricksix->setTrickgroup('Intermediate');
        
        $imageLink = new ImageLink();
        $imageLink->setContent('https://preview.redd.it/95hh5e0c0tf11.jpg?width=960&crop=smart&auto=webp&v=enabled&s=dce81794a8ba6ce95bf7ee4651c23d2316e70b2a');
        $tricksix->addImageLink($imageLink);
        $imageLink2 = new ImageLink();
        $imageLink2->setContent('https://assets2.rockpapershotgun.com/obv.jpg/BROK/resize/1920x1920%3E/format/jpg/quality/80/obv.jpg');
        $tricksix->addImageLink($imageLink2);
        $videoLink = new VideoLink();
        $videoLink->setContent('https://www.youtube.com/watch?v=kOyCsY4rBH0');
        $tricksix->addVideoLink($videoLink);
        $videoLink2 = new VideoLink();
        $videoLink2->setContent('https://www.youtube.com/watch?v=ZsNPUaYV4PM');
        $tricksix->addVideoLink($videoLink2);
        
        $tricksix->setCreatedAt(new DateTimeImmutable());
        $tricksix->setmodifiedAt(new DateTimeImmutable());
        $tricksix->setDeleted(0);
        $tricksix->setDiscussionChannel('empty');

        $manager->persist($tricksix);

        $trickseven = new Trick();
        $trickseven->setName('Frontside 180');
        $trickseven->setCreator($user);
        $trickseven->setDescription('Frontside 180 is a trick that involves rotating the b  oard 180 degrees in a frontside direction while in the air.');
        $trickseven->setTrickgroup('Intermediate');
        
        $imageLink = new ImageLink();
        $imageLink->setContent('https://coresites-cdn-adm.imgix.net/whitelines_new/wp-content/uploads/2013/09/13-620x413.jpg?fit=crop&w=620&h=413');
        $trickseven->addImageLink($imageLink);
        $imageLink2 = new ImageLink();
        $imageLink2->setContent('https://assets2.rockpapershotgun.com/obv.jpg/BROK/resize/1920x1920%3E/format/jpg/quality/80/obv.jpg');
        $trickseven->addImageLink($imageLink2);
        $videoLink = new VideoLink();
        $videoLink->setContent('https://www.youtube.com/watch?v=kOyCsY4rBH0');
        $trickseven->addVideoLink($videoLink);
        $videoLink2 = new VideoLink();
        $videoLink2->setContent('https://www.youtube.com/watch?v=ZsNPUaYV4PM');
        $trickseven->addVideoLink($videoLink2);

        $trickseven->setCreatedAt(new DateTimeImmutable());
        $trickseven->setmodifiedAt(new DateTimeImmutable());
        $trickseven->setDeleted(0);
        $trickseven->setDiscussionChannel('empty');
        
        $manager->persist($trickseven);



        $trickeight = new Trick();
        $trickeight->setName('Backside Boardslide');
        $trickeight->setCreator($user);
        $trickeight->setDescription('Backside Boardslide is a trick that involves sliding the board along a rail or box with the backside of the board facing the obstacle.');
        $trickeight->setTrickgroup('Advanced');
        
        $imageLink = new ImageLink();
        $imageLink->setContent('https://media02.stockfood.com/largepreviews/MjE3MTg4NzQ5Nw==/70060887-A-young-man-on-a-snowboard-makes-a-trick-a-Backside-Boardslide-on-an-A-Frame-Box-at-the-funpark-snowland.jpg');
        $trickeight->addImageLink($imageLink);
        $imageLink2 = new ImageLink();
        $imageLink2->setContent('https://assets2.rockpapershotgun.com/obv.jpg/BROK/resize/1920x1920%3E/format/jpg/quality/80/obv.jpg');
        $trickeight->addImageLink($imageLink2);
        $videoLink = new VideoLink();
        $videoLink->setContent('https://www.youtube.com/watch?v=kOyCsY4rBH0');
        $trickeight->addVideoLink($videoLink);
        $videoLink2 = new VideoLink();
        $videoLink2->setContent('https://www.youtube.com/watch?v=ZsNPUaYV4PM');
        $trickeight->addVideoLink($videoLink2);

        $trickeight->setCreatedAt(new DateTimeImmutable());
        $trickeight->setmodifiedAt(new DateTimeImmutable());
        $trickeight->setDeleted(0);
        $trickeight->setDiscussionChannel('empty');
        
        $manager->persist($trickeight);


        
        $tricknine = new Trick();
        $tricknine->setName('Frontside Boardslide');
        $tricknine->setCreator($user);
        $tricknine->setDescription('Frontside Boardslide is a trick that involves sliding the board along a rail or box with the frontside of the board facing the obstacle.');
        $tricknine->setTrickgroup('Intermediate');
        
        $imageLink = new ImageLink();
        $imageLink->setContent('https://cdn.shopify.com/s/files/1/0230/2239/articles/Snowboard_Trick_Terminology.jpg?v=1556396922');
        $tricknine->addImageLink($imageLink);
        $imageLink2 = new ImageLink();
        $imageLink2->setContent('https://assets2.rockpapershotgun.com/obv.jpg/BROK/resize/1920x1920%3E/format/jpg/quality/80/obv.jpg');
        $tricknine->addImageLink($imageLink2);
        $videoLink = new VideoLink();
        $videoLink->setContent('https://www.youtube.com/watch?v=kOyCsY4rBH0');
        $tricknine->addVideoLink($videoLink);
        $videoLink2 = new VideoLink();
        $videoLink2->setContent('https://www.youtube.com/watch?v=ZsNPUaYV4PM');
        $tricknine->addVideoLink($videoLink2);

        $tricknine->setCreatedAt(new DateTimeImmutable());
        $tricknine->setmodifiedAt(new DateTimeImmutable());
        $tricknine->setDeleted(0);
        $tricknine->setDiscussionChannel('empty');

        $manager->persist($tricknine);

        $trickten = new Trick();
        $trickten->setName('Tripod');
        $trickten->setCreator($user);
        $trickten->setDescription('Tripod is where you use ');
        $trickten->setTrickgroup('Intermediate');

        $imageLink3 = new ImageLink();
        $imageLink3->setContent('https://www.burton.com/static/community/advice/snowboard-tricks-to-learn-now.jpg');
        $trickten->addImageLink($imageLink3);
        $imageLink4 = new ImageLink();
        $imageLink4->setContent('https://www.burton.com/static/community/advice/snowboard-tricks-to-learn-now.jpg');
        $trickten->addImageLink($imageLink4);
        $videoLink3 = new VideoLink();
        $videoLink3->setContent('https://www.youtube.com/watch?v=kOyCsY4rBH0');
        $trickten->addVideoLink($videoLink3);
        $videoLink4 = new VideoLink();
        $videoLink4->setContent('https://www.youtube.com/watch?v=kOyCsY4rBH0');
        $trickten->addVideoLink($videoLink4);

        $trickten->setCreatedAt(new DateTimeImmutable());
        $trickten->setModifiedAt(new DateTimeImmutable());
        $trickten->setDeleted(0);
        $trickten->setDiscussionChannel('empty');

        $manager->persist($trickten);

        $trickEleven = new Trick();
        $trickEleven->setName('Nose-Roll 180');
        $trickEleven->setCreator($user);
        $trickEleven->setDescription('Nose-Roll 180 is a trick where the rider spins 180 degrees while pressing the nose of the board against the ground, effectively pivoting on the nose.');
        $trickEleven->setTrickgroup('Intermediate');

        $imageLink5 = new ImageLink();
        $imageLink5->setContent('https://cdn.shopify.com/s/files/1/0230/2239/files/6_2bd24f17-d4d8-42ec-bdd0-d6875cf7bc62_large.jpg?v=1533599570');
        $trickEleven->addImageLink($imageLink5);
        $imageLink6 = new ImageLink();
        $imageLink6->setContent('https://cdn.shopify.com/s/files/1/0230/2239/files/6_2bd24f17-d4d8-42ec-bdd0-d6875cf7bc62_large.jpg?v=1533599570');
        $trickEleven->addImageLink($imageLink6);
        $videoLink5 = new VideoLink();
        $videoLink5->setContent('https://www.youtube.com/watch?v=kOyCsY4rBH0');
        $trickEleven->addVideoLink($videoLink5);
        $videoLink6 = new VideoLink();
        $videoLink6->setContent('https://www.youtube.com/watch?v=kOyCsY4rBH0');
        $trickEleven->addVideoLink($videoLink6);

        $trickEleven->setCreatedAt(new DateTimeImmutable());
        $trickEleven->setModifiedAt(new DateTimeImmutable());
        $trickEleven->setDeleted(0);
        $trickEleven->setDiscussionChannel('empty');

        $manager->persist($trickEleven);


        $trickTwelve = new Trick();
        $trickTwelve->setName('Tail-Block 180');
        $trickTwelve->setCreator($user);
        $trickTwelve->setDescription('Tail-Block 180 is a trick where the rider spins 180 degrees while pressing the tail of the board against the ground, effectively pivoting on the tail.');
        $trickTwelve->setTrickgroup('Intermediate');

        $imageLink7 = new ImageLink();
        $imageLink7->setContent('https://cdn.shopify.com/s/files/1/0230/2239/articles/VE_00735_-_Nev_Lapwood_2048px_by_Vince_Emond_900x.jpg?v=1578080077');
        $trickTwelve->addImageLink($imageLink7);
        $videoLink7 = new VideoLink();
        $videoLink7->setContent('https://www.youtube.com/watch?v=kOyCsY4rBH0');
        $trickTwelve->addVideoLink($videoLink7);

        $trickTwelve->setCreatedAt(new DateTimeImmutable());
        $trickTwelve->setModifiedAt(new DateTimeImmutable());
        $trickTwelve->setDeleted(0);
        $trickTwelve->setDiscussionChannel('empty');

        $manager->persist($trickTwelve);

        $trickThirteen = new Trick();
        $trickThirteen->setName('Backflip');
        $trickThirteen->setCreator($user);
        $trickThirteen->setDescription('Backflip is an advanced trick where the rider jumps and rotates backward in a complete 360-degree flip.');
        $trickThirteen->setTrickgroup('Advanced');

        $imageLink8 = new ImageLink();
        $imageLink8->setContent('https://www.sony.eu/alphauniverse/assets/resized/2020/12/jaakko-posti-sony-alpha-9-snowboarder-performs-an-impressive-backflip-582x836-1_portrait_582x836.jpg');
        $trickThirteen->addImageLink($imageLink8);
        $videoLink8 = new VideoLink();
        $videoLink8->setContent('https://www.youtube.com/watch?v=kOyCsY4rBH0');
        $trickThirteen->addVideoLink($videoLink8);

        $trickThirteen->setCreatedAt(new DateTimeImmutable());
        $trickThirteen->setModifiedAt(new DateTimeImmutable());
        $trickThirteen->setDeleted(0);
        $trickThirteen->setDiscussionChannel('empty');

        $manager->persist($trickThirteen);


        $trickFourteen = new Trick();
        $trickFourteen->setName('Frontflip');
        $trickFourteen->setCreator($user);
        $trickFourteen->setDescription('Frontflip is an advanced trick where the rider jumps and rotates forward in a complete 360-degree flip.');
        $trickFourteen->setTrickgroup('Advanced');

        $imageLink9 = new ImageLink();
        $imageLink9->setContent('https://coresites-cdn-adm.imgix.net/whitelines_new/wp-content/uploads/2012/12/IMG_7635-620x413.jpg');
        $trickFourteen->addImageLink($imageLink9);
        $videoLink9 = new VideoLink();
        $videoLink9->setContent('https://www.youtube.com/watch?v=kOyCsY4rBH0');
        $trickFourteen->addVideoLink($videoLink9);

        $trickFourteen->setCreatedAt(new DateTimeImmutable());
        $trickFourteen->setModifiedAt(new DateTimeImmutable());
        $trickFourteen->setDeleted(0);
        $trickFourteen->setDiscussionChannel('empty');

        $manager->persist($trickFourteen);

        $trickFifteen = new Trick();
        $trickFifteen->setName('Method Grab');
        $trickFifteen->setCreator($user);
        $trickFifteen->setDescription('Method Grab is a stylish grab trick where the rider grabs the heel edge of the snowboard with their front hand while bending their knees and extending the back leg.');
        $trickFifteen->setTrickgroup('Intermediate');

        $imageLink10 = new ImageLink();
        $imageLink10->setContent('https://ridestoremagazine.imgix.net/http%3A%2F%2Fwordpress-604950-1959020.cloudwaysapps.com%2Fwp-content%2Fuploads%2F2022%2F04%2Fjoh01043-v2.jpg?ixlib=gatsbySourceUrl-1.6.9&auto=format%2Ccompress&w=689&h=861&s=5b283f8575471fa62b738080a2812006');
        $trickFifteen->addImageLink($imageLink10);
        $videoLink10 = new VideoLink();
        $videoLink10->setContent('https://www.youtube.com/watch?v=exampleMethodGrabVideo1');
        $trickFifteen->addVideoLink($videoLink10);

        $trickFifteen->setCreatedAt(new DateTimeImmutable());
        $trickFifteen->setModifiedAt(new DateTimeImmutable());
        $trickFifteen->setDeleted(0);
        $trickFifteen->setDiscussionChannel('empty');

        $manager->persist($trickFifteen);



        $manager->flush();
    }
}
