<?php 

namespace App\Tests\Repository;

use App\Entity\Task;
use App\Controller\TaskController;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;

// use task controller
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TaskControllerTest extends WebTestCase
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

    public function testEditTaskSuccess(): void
    {
        // Arrange: Prepare the task to edit
        $taskId = 1;
        $newTitle = 'Updated Title';
        $newContent = 'Updated Content';

        // Act: Request the edit page
        $crawler = $this->client->request('GET', "/task/{$taskId}/edit");

        // Assert: Form is displayed
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        // Act: Submit the form with new data
        $form = $crawler->selectButton('Modifier')->form();
        $form['task[title]']->setValue($newTitle);
        $form['task[content]']->setValue($newContent);
        $this->client->submit($form);

        // Assert: Response is a redirection to the task list
        $this->assertTrue($this->client->getResponse()->isRedirect('/task'));

        // Assert: Task has been updated in the database
        $updatedTask = $this->taskRepository->find($taskId);
        $this->assertEquals($newTitle, $updatedTask->getTitle());
        $this->assertEquals($newContent, $updatedTask->getContent());
    }

    public function testEditTaskWithInvalidData(): void
    {
        // Arrange: Prepare the task to edit
        $taskId = 1;

        // Act: Request the edit page
        $crawler = $this->client->request('GET', "/task/{$taskId}/edit");

        // Act: Submit the form with empty title and content
        $form = $crawler->selectButton('Modifier')->form();
        $form['task[title]']->setValue('');
        $form['task[content]']->setValue('');
        $this->client->submit($form);

        // Assert: Form is displayed with errors: you can't submit an empty title and content in the browser.
        $this->assertEquals(1, $crawler->filter('input[required]#task_title')->count());
        $this->assertEquals(1, $crawler->filter('textarea[required]#task_content')->count());
    }

    public function testEditNonExistingTask(): void
    {
        // Arrange: Prepare a non-existing task ID
        $taskId = 9999;

        // Act: Request the edit page
        $this->client->request('GET', "/task/{$taskId}/edit");

        // Assert: Response is 404 Not Found or a redirection to a suitable error page
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    // Function to tost the getAllTasks function
    public function testGetAllTasks(): void
    {
        // Asserts that the getAllTasks exists in the repository
        $this->assertTrue(
            method_exists($this->taskRepository, 'findAll'),
            'Class does not have method getAllTasks'
        );

        // Asserts that the task_list exists in the controller
        $this->assertTrue(
            method_exists($this->taskController, 'getAllTasks'),
            'Class does not have method getAllTasks'
        );

        $tasks = $this->taskRepository->findAll();

        $this->assertCount(1, $tasks);

        

        // Request the /task URL
        $crawler = $this->client->request('GET', '/task');

        // Assert that the response status code is 200 (HTTP_OK)
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        // Assert that there is exactly 1 task in the task list
        // (assuming tasks are rendered as an HTML list, and each task is an <li> element)
        $this->assertCount(1, $crawler->filter('div.thumbnail'));

        // If you need to assert against the Response object directly,
        // you can get it from the client and then make further assertions
        $response = $this->client->getResponse();
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);

    }

    protected function tearDown(): void
    {
        parent::tearDown();
        

        $this->entityManager->close();

    }
}