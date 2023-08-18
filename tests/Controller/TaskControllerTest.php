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

        $this->entityManager->getConnection()->executeStatement('DELETE FROM task');
        // reset the auto-increment
        $this->entityManager->getConnection()->executeStatement('ALTER TABLE task AUTO_INCREMENT = 1');

        // create a task
        $task = new Task(
            'title',
            'content'
        );

        $this->taskRepository->save($task, true);
    }

    public function testgetAllTasks(): void
    {

        $tasks = $this->taskRepository->findAll();

        $this->assertCount(1, $tasks);
    }

    // function to test the task_toggle function
    public function testTaskToggle(): void
    {
        $task = $this->taskRepository->find(1);
        $task->toggle(!$task->getIsDone());
        $this->taskRepository->save($task, true);

        $this->assertSame(true, $task->getIsDone());
    }

    // function to test the task_delete function
    public function testTaskDelete(): void
    {
        $task = $this->taskRepository->find(1);
        $this->taskRepository->remove($task, true);

        $this->assertCount(0, $this->taskRepository->findAll());
    }

    // function to test the task_create function
    public function testTaskCreate(): void
    {

        // tests if the createTask function exists
        $this->assertTrue(
            method_exists($this->taskRepository, 'createTask'),
            'Class does not have method createTask'
        );

        // asserts that the task_create function exist in task controller
        $this->assertTrue(
            method_exists($this->taskRepository, 'createTask'),
            'Class does not have method task_create'
        );

        $initTaskCount = count($this->taskRepository->findAll());


        $task = new Task(
            'title',
            'content'
        );

        $this->taskRepository->save($task, true);

        $this->assertCount($initTaskCount + 1, $this->taskRepository->findAll());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        

        $this->entityManager->close();

    }
}