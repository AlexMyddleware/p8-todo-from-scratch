<?php

namespace App\Tests\Entity;

use App\Entity\Task;
use App\Entity\User;
use App\Controller\TaskController;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskTest extends WebTestCase
{

    private EntityManagerInterface $entityManager;
    private TaskRepository $taskRepository;
    private TaskController $taskController;
    private $client;

    protected function setUp(): void
    {
        // $kernel = self::bootKernel();

        // create client
        $this->client = static::createClient();

        $this->entityManager = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->taskRepository = $this->entityManager
            ->getRepository(Task::class);

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
    public function testSetCreatedAt()
    {
        $task = new Task();
        $date = new \DateTimeImmutable('2022-08-25 12:00:00');

        $task->setCreatedAt($date);

        $this->assertEquals($date, $task->getCreatedAt());
    }

    public function testGetCreatedBy()
    {
        $task = new Task();

        $crawler = $this->client->request('GET', '/login');

        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => 'anonymous@gmail.com',
            '_password' => 'password',
        ]);

        $this->client->submit($form);
        
        $this->assertTrue($this->client->getResponse()->isRedirect());
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        // test that the site contains the logout button
        $this->assertSelectorExists('a[href="/logout"]');

        // get the id of the current user
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'anonymous@gmail.com']);
        $userId = $user->getId();

        $task->setCreatedBy($user);

        $this->assertEquals($userId, $task->getCreatedBy()->getId());

        
    }
}
