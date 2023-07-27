<?php 

namespace App\Tests\Repository;

use App\Entity\Task;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TaskRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private TaskRepository $taskRepository;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->taskRepository = $this->entityManager
            ->getRepository(Task::class);
    }

    public function testSaveTask(): void
    {
        $task = new Task(
            'title',
            'content'
        );

        $this->taskRepository->save($task, true);

        $this->assertNotNull($task->getId());
        $this->assertSame('title', $task->getTitle());
        $this->assertSame('content', $task->getContent());
        $this->assertSame(false, $task->getIsDone());
        $this->assertInstanceOf(\DateTimeInterface::class, $task->getCreatedAt());
    }

    public function testRemoveTask(): void
    {
        $task = new Task(
            'title',
            'content'
        );

        $this->taskRepository->save($task, true);

        $id = $task->getId();

        $this->taskRepository->remove($task, true);

        $task = $this->taskRepository->find($id);

        $this->assertNull($task);
    }

    public function testFindAll(): void
    {
        $tasks = $this->taskRepository->findAll();

        $this->assertIsArray($tasks);
    }

    public function testFindOneBy(): void
    {
        $task = $this->taskRepository->findOneBy(['title' => 'title']);

        $this->assertSame('title', $task->getTitle());
    }

    public function testFindBy(): void
    {
        $tasks = $this->taskRepository->findBy(['title' => 'title']);

        $this->assertIsArray($tasks);
    }

    public function testFind(): void
    {
        $task = $this->taskRepository->find(1);

        $this->assertSame('title', $task->getTitle());
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager->close();
    }
}