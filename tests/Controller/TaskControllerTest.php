<?php 

namespace App\Tests\Repository;

use App\Entity\Task;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

// use task controller
use App\Controller\TaskController;

class TaskControllerTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private TaskRepository $taskRepository;
    private TaskController $taskController;

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

        // create a task controller
        $this->taskController = new TaskController($this->taskRepository);
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
            method_exists($this->taskController, 'task_create'),
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

    // function to test the edit function for a task
    public function testTaskEdit(): void
    {
        // Asserts that the editTask exists in the repository
        $this->assertTrue(
            method_exists($this->taskRepository, 'editTask'),
            'Class does not have method editTask'
        );

        // Asserts that the task_edit exists in the controller
        $this->assertTrue(
            method_exists($this->taskController, 'task_edit'),
            'Class does not have method task_edit'
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        

        $this->entityManager->close();

    }
}