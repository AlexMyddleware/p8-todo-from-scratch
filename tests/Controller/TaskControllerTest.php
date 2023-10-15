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

        $this->loginAsNormalUser();
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

    // public function to test when a unverified user try to see all the tasks
    public function testGetAllTasksUnverified(): void
    {
        
        $crawler = $this->client->request('GET', '/login');

        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => 'notloggedin@gmail.com',
            '_password' => 'Password1@',
        ]);

        $this->client->submit($form);

        $crawler = $this->client->request('GET', '/task');

        $this->assertTrue($this->client->getResponse()->isRedirect('/'));
    }

    // public function to test getting all the completed tasks
    public function testGetCompletedTasks(): void
    {
        $this->loginAsNormalUser();

        // create 2 completed tasks
        $task1 = new Task(
            'task1',
            'content1'
        );

        $task2 = new Task(
            'task2',
            'content2'
        );

        $task1->toggle(!$task1->getIsDone());

        $task2->toggle(!$task2->getIsDone());

        $this->taskRepository->save($task1, true);

        $this->taskRepository->save($task2, true);

        // Request the /task URL
        $crawler = $this->client->request('GET', '/task/completed');

        // Assert that the response status code is 200 (HTTP_OK)
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        // Assert that there is exactly 0 task in the task list
        // (assuming tasks are rendered as an HTML list, and each task is an <li> element)
        $this->assertCount(2, $crawler->filter('div.thumbnail'));

        // If you need to assert against the Response object directly,
        // you can get it from the client and then make further assertions
        $response = $this->client->getResponse();
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
    }

    public function testEditTaskWithInvalidData(): void
    {

        $this->loginAsNormalUser();

        $task = new Task(
            'task to be edited with invalid data',
            'content to be edited with invalid data'
        );

        $this->taskRepository->save($task, true);

        $taskId = $task->getId();
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

        $this->loginAsNormalUser();
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

        $this->loginAsNormalUser();

        $task = new Task(
            'task to be switched once',
            'content to be switched once'
        );

        $this->taskRepository->save($task, true);

        $taskId = $task->getId();

        $task = $this->taskRepository->find($taskId);
        $initialIsDone = $task->getIsDone();

        // Act: Request the toggle page
        $this->client->request('GET', "/task/{$taskId}/toggle");

        // Assert: Response is a redirection to the task list
        $this->assertTrue($this->client->getResponse()->isRedirect('/task'));

        // refresh the task
        $this->entityManager->refresh($task);

        // Assert: Task has been updated in the database
        $updatedTask = $this->taskRepository->find($taskId);
        $this->assertNotEquals($initialIsDone, $task->getIsDone());
    }

    // Function to test the toggle function for a task by setting the task to done and then to not done
    public function testTaskToggleTwice(): void
    {

        $this->loginAsNormalUser();

        $task = new Task(
            'task to be switched twice',
            'content to be switched twice'
        );

        $this->taskRepository->save($task, true);

        $taskId = $task->getId();
        $task = $this->taskRepository->find($taskId);
        $initialIsDone = $task->getIsDone();

        // Act: Request the toggle page
        $this->client->request('GET', "/task/{$taskId}/toggle");

        // Assert: Response is a redirection to the task list
        $this->assertTrue($this->client->getResponse()->isRedirect('/task'));

        // refresh the task
        $this->entityManager->refresh($task);

        // Assert: Task has been updated in the database
        $updatedTask = $this->taskRepository->find($taskId);
        $this->assertNotEquals($initialIsDone, $updatedTask->getIsDone());

        // Act: Request the toggle page
        $this->client->request('GET', "/task/{$taskId}/toggle");

        // Assert: Response is a redirection to the task list
        $this->assertTrue($this->client->getResponse()->isRedirect('/task'));

        // refresh the task
        $this->entityManager->refresh($task);


        // Assert: Task has been updated in the database
        $updatedTask = $this->taskRepository->find($taskId);
        $this->assertEquals($initialIsDone, $updatedTask->getIsDone());
    }

    // Function to test the delete function for a task with not done
    public function testTaskDeleteFailed(): void
    {

        $this->loginAsNormalUser();
        // Arrange: Prepare the task to delete

        $task = new Task(
            'task to be deletedfailure',
            'content deleted failure'
        );

        $this->taskRepository->save($task, true);

        $taskId = $task->getId();
        $task = $this->taskRepository->find($taskId);

        // Act: Request the delete page
        $this->client->request('GET', "/task/{$taskId}/delete");

        // Assert: Response is a redirection to the task list
        $this->assertTrue($this->client->getResponse()->isRedirect('/task'));

        // Assert: Task has been deleted in the database
        $deletedTask = $this->taskRepository->find($taskId);
        $this->assertNotNull($deletedTask);
    }

    public function testTaskDeleteAnonymousTaskFailureNotAdmin()
    {
        // Arrange: Prepare the task to delete
        // create one task with the author being anonymous
        $taskAnonymous = new Task(
            'Task anonymous',
            'content2, task can be deleted by anonymous'
        );

        // get the user anonymous
        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'anonymous@gmail.com']);

        // set the user anonymous as the author of the task
        $taskAnonymous->setCreatedBy($user);

        // persist the task
        $this->entityManager->persist($taskAnonymous);

        // flush
        $this->entityManager->flush();
        
        // log in as the author of the task
        $crawler = $this->client->request('GET', '/login');

        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => 'normaluser@gmailcom',
            '_password' => 'passwordnormal',
        ]);

        $this->client->submit($form);

        // get the task id
        $taskId = $taskAnonymous->getId();

        // Act: Request the delete page
        $this->client->request('GET', "/task/{$taskId}/delete");

        // Assert: Response is a redirection to the task list
        $this->assertTrue($this->client->getResponse()->isRedirect('/task'));

        // Assert: Task has been deleted in the database
        $deletedTask = $this->taskRepository->find($taskId);
        $this->assertNotNull($deletedTask);
    }

    public function testTaskDeleteFailureNotTheAuthor()
    {
        // Arrange: Prepare the task to delete
        // create one task with the author being anonymous
        $taskNormal = new Task(
            'Task normal can not delete',
            'content2, task can be deleted by normal but not by anonymous'
        );

        // get the user anonymous
        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => 'normaluser@gmailcom']);

        // set the user anonymous as the author of the task
        $taskNormal->setCreatedBy($user);

        // persist the task
        $this->entityManager->persist($taskNormal);

        // flush
        $this->entityManager->flush();
        
        // log in as the author of the task
        $crawler = $this->client->request('GET', '/login');

        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => 'anonymous@gmail.com',
            '_password' => 'password',
        ]);

        $this->client->submit($form);

        // get the task id
        $taskId = $taskNormal->getId();

        // Act: Request the delete page
        $this->client->request('GET', "/task/{$taskId}/delete");

        // Assert: Response is a redirection to the task list
        $this->assertTrue($this->client->getResponse()->isRedirect('/task'));

        // Assert: Task has been deleted in the database
        $deletedTask = $this->taskRepository->find($taskId);
        $this->assertNotNull($deletedTask);
    }

    // function to test if the delete function for a task with done works because the user is the author of the task
    public function testTaskDeleteCorrectAuthor()
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

    public function loginAsNormalUser()
    {
        // log in as the author of the task
        $crawler = $this->client->request('GET', '/login');

        $form = $crawler->selectButton('Se connecter')->form([
            '_username' => 'normaluser@gmailcom',
            '_password' => 'passwordnormal',
        ]);

        $this->client->submit($form);

        // follow redirect
        $this->client->followRedirect();
    }


    // Function to tost the getAllTasks function
    public function testGetAllTasks(): void
    {
        $this->loginAsNormalUser();

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
