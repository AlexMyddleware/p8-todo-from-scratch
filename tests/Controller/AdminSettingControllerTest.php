<?php 

namespace App\Tests\Repository;

use App\Entity\Task;
use App\Entity\User;
use App\Controller\TaskController;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
// use task controller
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminSettingControllerTest extends WebTestCase
{
    private EntityManagerInterface $entityManager;
    private TaskRepository $taskRepository;
    private TaskController $taskController;
    private UserRepository $userRepository;
    private $client;

    protected function setUp(): void
    {
        // create client
        $this->client = static::createClient();

        $this->entityManager = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->taskRepository = $this->entityManager
            ->getRepository(Task::class);

        $this->userRepository = $this->entityManager
            ->getRepository(User::class);

        $this->entityManager->getConnection()->executeStatement('DELETE FROM task');
        // reset the auto-increment
        $this->entityManager->getConnection()->executeStatement('ALTER TABLE task AUTO_INCREMENT = 1');

        // create a task
        $task = new Task(
            'title',
            'content'
        );

        $this->taskRepository->save($task, true);

        // create a task controller
        $this->taskController = new TaskController($this->taskRepository);
    }


    // Function to verify that when the user is logged in as an admin, the link to modify the users is present
    public function testAdminLink(): void
    {
        $crawler = $this->client->request('GET', '/login');

        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => 'adminuser@gmail.com',
            '_password' => 'passwordadmin',
        ]);

        $this->client->submit($form);
        
        $this->assertTrue($this->client->getResponse()->isRedirect());
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        // test that the site contains the logout button
        $this->assertSelectorExists('a[href="/logout"]');

        // asserts that the user role is admin
        // dump($this->client->getContainer()->get('security.token_storage')->getToken()->getUser()->getRoles());
        $this->assertContains('ROLE_ADMIN', $this->client->getContainer()->get('security.token_storage')->getToken()->getUser()->getRoles());

        // assert that the user is an admin
        $this->assertSelectorExists('a[href="/admin"]');    
    }

    // Function to verify that when the user is logged in as an admin, the link to modify the users is present
    public function testAdminLinkNotPresent(): void
    {
        $crawler = $this->client->request('GET', '/login');

        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => 'anonymous@gmail.com', // user is not an admin
            '_password' => 'password',
        ]);

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $this->client->followRedirect();

        $this->assertResponseIsSuccessful();

        // test that the site contains the logout button
        $this->assertSelectorExists('a[href="/logout"]');

        // asserts that the user role is not admin

        $this->assertNotContains('ROLE_ADMIN', $this->client->getContainer()->get('security.token_storage')->getToken()->getUser()->getRoles());

        // assert that the user is not an admin

        $this->assertSelectorNotExists('a[href="/admin"]');

    }

    // Function to verify that if someone tries to access the page of a user without being an admin, he is redirected to the home page
    public function testAdminLinkNotAdmin(): void
    {
        $crawler = $this->client->request('GET', '/login');

        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => 'anonymous@gmail.com', // user is not an admin
            '_password' => 'password',
        ]);

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $this->client->followRedirect();

        $this->assertResponseIsSuccessful();

        // test that the site contains the logout button

        $this->assertSelectorExists('a[href="/logout"]');

        // asserts that the user role is not admin

        $this->assertNotContains('ROLE_ADMIN', $this->client->getContainer()->get('security.token_storage')->getToken()->getUser()->getRoles());

        // assert that the user is not an admin

        $this->assertSelectorNotExists('a[href="/admin"]');

        // get the user with the email anonymous@gmail.com using the entity
        
        $user = $this->userRepository->findOneBy(['email' => 'anonymous@gmail.com']);
        // get the id of the user
        $id = $user->getId();

        // try to access the page of a user

        $this->client->request('GET', '/admin/' . $id);

        $this->client->followRedirect();

        $this->assertResponseIsSuccessful();

        // assert that the user is redirected to the home page

        $this->assertSelectorExists('a[href="/logout"]');

    }

    // Function to verify that if someone tries to access the edit of a user without being an admin, he is redirected to the home page
    public function testAdminLinkNotAdminEdit(): void
    {
        $crawler = $this->client->request('GET', '/login');

        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => 'anonymous@gmail.com', // user is not an admin
            '_password' => 'password',
        ]);

        $this->client->submit($form);

        $this->assertTrue($this->client->getResponse()->isRedirect());

        $this->client->followRedirect();

        $this->assertResponseIsSuccessful();

        // test that the site contains the logout button

        $this->assertSelectorExists('a[href="/logout"]');

        // asserts that the user role is not admin

        $this->assertNotContains('ROLE_ADMIN', $this->client->getContainer()->get('security.token_storage')->getToken()->getUser()->getRoles());

        // assert that the user is not an admin

        $this->assertSelectorNotExists('a[href="/admin"]');

        // get the user with the email anonymous@gmail using the entity

        $user = $this->userRepository->findOneBy(['email' => 'anonymous@gmail.com']);

        // get the id of the user

        $id = $user->getId();

        // try to access the edit of a user

        $this->client->request('GET', '/admin/' . $id . '/edit');

        $this->client->followRedirect();

        $this->assertResponseIsSuccessful();

        // assert that the user is redirected to the home page

        $this->assertSelectorExists('a[href="/logout"]');

    }
    





    protected function tearDown(): void
    {
        parent::tearDown();
        

        $this->entityManager->close();

    }
}