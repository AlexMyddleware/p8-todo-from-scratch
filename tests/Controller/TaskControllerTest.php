<?php

namespace App\Tests\Repository;

use App\Entity\Task;
use App\Entity\User;
use App\Controller\TaskController;
use App\Repository\TaskRepository;

// use task controller
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

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
        // // reset the auto-increment
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

    // function to test the task_create function
    public function testTaskCreateIsFunctionPresent(): void
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

    // Function to test the task_create function in the controller
    public function testTaskCreateSuccess(): void
    {

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


        // Arrange: Prepare the task to create
        $newTitle = 'New Title';
        $newContent = 'New Content';

        // Act: Request the create page
        $crawler = $this->client->request('GET', '/task/create');

        // Act: Submit the form with new data
        $form = $crawler->selectButton('Ajouter')->form();
        $form['task[title]']->setValue($newTitle);
        $form['task[content]']->setValue($newContent);

        $this->client->submit($form);

        // Assert: Response is a redirection to the task list
        $this->assertTrue($this->client->getResponse()->isRedirect('/task'));

        // Assert: Task has been created in the database
        $createdTask = $this->taskRepository->findOneBy(['title' => $newTitle]);
        $this->assertNotNull($createdTask);
        $this->assertEquals($newTitle, $createdTask->getTitle());
        $this->assertEquals($newContent, $createdTask->getContent());
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

    // Function to test the toggle function for a task
    public function testTaskToggleSuccess(): void
    {
        // Arrange: Prepare the task to toggle
        $taskId = 1;
        $task = $this->taskRepository->find($taskId);
        $initialIsDone = $task->getIsDone();

        // Act: Request the toggle page
        $this->client->request('GET', "/task/{$taskId}/toggle");

        // Assert: Response is a redirection to the task list
        $this->assertTrue($this->client->getResponse()->isRedirect('/task'));

        // Assert: Task has been updated in the database
        $updatedTask = $this->taskRepository->find($taskId);
        $this->assertNotEquals($initialIsDone, $updatedTask->getIsDone());
    }

    // Function to test the toggle function for a task by setting the task to done and then to not done
    public function testTaskToggleTwice(): void
    {
        // Arrange: Prepare the task to toggle
        $taskId = 1;
        $task = $this->taskRepository->find($taskId);
        $initialIsDone = $task->getIsDone();

        // Act: Request the toggle page
        $this->client->request('GET', "/task/{$taskId}/toggle");

        // Assert: Response is a redirection to the task list
        $this->assertTrue($this->client->getResponse()->isRedirect('/task'));

        // Assert: Task has been updated in the database
        $updatedTask = $this->taskRepository->find($taskId);
        $this->assertNotEquals($initialIsDone, $updatedTask->getIsDone());

        // Act: Request the toggle page
        $this->client->request('GET', "/task/{$taskId}/toggle");

        // Assert: Response is a redirection to the task list
        $this->assertTrue($this->client->getResponse()->isRedirect('/task'));

        // Assert: Task has been updated in the database
        $updatedTask = $this->taskRepository->find($taskId);
        $this->assertEquals($initialIsDone, $updatedTask->getIsDone());
    }

    // Function to test the delete function for a task with not done
    public function testTaskDeleteFailed(): void
    {
        // Arrange: Prepare the task to delete
        $taskId = 1;
        $task = $this->taskRepository->find($taskId);

        // Act: Request the delete page
        $this->client->request('GET', "/task/{$taskId}/delete");

        // Assert: Response is a redirection to the task list
        $this->assertTrue($this->client->getResponse()->isRedirect('/task'));

        // Assert: Task has been deleted in the database
        $deletedTask = $this->taskRepository->find($taskId);
        $this->assertNotNull($deletedTask);
    }

    // function to test if the delete function for a task with done works because the user is the author of the task
    public function testTaskCorrectAuthor()
    {
        // Arrange: Prepare the task to delete
        // create one task with the author being adminuserroleremoved@gmail.com
        $task2 = new Task(
            'Task normal user',
            'content2, task can be deleted by normaluser'
        );

        // get the user normaluser@gmailcom
        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'normaluser@gmailcom']);

        // set the user as the author of the task
        $task2->setCreatedBy($user);

        // persist the task
        $this->entityManager->persist($task2);

        // flush
        $this->entityManager->flush();
        

        $userId = $user->getId();

        // get the task id
        $taskId = $task2->getId();

        // log in as the author of the task
        $crawler = $this->client->request('GET', '/login');

        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => 'normaluser@gmailcom',
            '_password' => 'passwordnormal',
        ]);

        $this->client->submit($form);

        // Act: Request the delete page
        $this->client->request('GET', "/task/{$taskId}/delete");

        // Assert: Response is a redirection to the task list
        $this->assertTrue($this->client->getResponse()->isRedirect('/task'));

        // Assert: Task has been deleted in the database
        $deletedTask = $this->taskRepository->find($taskId);
        $this->assertNull($deletedTask);

        
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
