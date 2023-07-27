<?php 

namespace App\Tests\Repository;

use App\Entity\Task;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TaskControllerTest extends KernelTestCase
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

    public function testgetAllTasks(): void
    {

        $tasks = $this->taskRepository->findAll();

        $this->assertCount(1, $tasks);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->getConnection()->executeStatement('DELETE FROM task');

        $this->entityManager->close();

    }
}