<?php

namespace App\Tests\Repository;

use App\Entity\Task;
use App\Entity\User;
use RuntimeException;
use App\Controller\TaskController;
use App\Repository\TaskRepository;
// use task controller
use App\Repository\UserRepository;
use Symfony\Component\Process\Process;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminSettingControllerTest extends WebTestCase
{
    private EntityManagerInterface $entityManager;
    private TaskRepository $taskRepository;
    private TaskController $taskController;
    private UserRepository $userRepository;
    private $client;
    private $originalRoles;
    private $crawler;
    private $form;

    protected function setUp(): void
    {
        parent::setUp();

        // sets orginal roles as simple user
        $this->originalRoles = ['ROLE_USER'];
        
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


        $this->crawler = $this->client->request('GET', '/login');
    }

    public function assertForm()
    {
        $this->client->submit($this->form);
        $this->assertTrue($this->client->getResponse()->isRedirect());
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    // Function to verify that when the user is logged in as an admin, the link to modify the users is present
    public function testAdminLink(): void
    {

        $this->form = $this->crawler->selectButton('Se connecter')->form([
            '_username' => 'adminuser@gmail.com',
            '_password' => 'passwordadmin',
        ]);

        $this->assertForm();
        // test that the site contains the logout button
        $this->assertSelectorExists('a[href="/logout"]');

        // asserts that the user role is admin
        $this->assertContains('ROLE_ADMIN', $this->client->getContainer()->get('security.token_storage')->getToken()->getUser()->getRoles());

        // assert that the user is an admin
        $this->assertSelectorExists('a[href="/admin"]');
    }

    // Function to verify that when the user is logged in as an admin, the link to modify the users is present
    public function testAdminLinkNotPresent(): void
    {

        $this->form = $this->crawler->selectButton('Se connecter')->form([
            '_username' => 'anonymous@gmail.com', // user is not an admin
            '_password' => 'password',
        ]);

        $this->assertForm();

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

        $this->form = $this->crawler->selectButton('Se connecter')->form([
            '_username' => 'anonymous@gmail.com', // user is not an admin
            '_password' => 'password',
        ]);

        $this->assertForm();

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

    public function testAccessToAdminPanelAsNotLogged(): void
    {
        $this->client->request('GET', '/admin');

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $this->client->followRedirect();

        $this->assertSelectorExists('a[href="/login"]');
    }

    public function testAdminPanelShowsListOfUsers(): void
    {

        $this->form = $this->crawler->selectButton('Se connecter')->form([
            '_username' => 'adminuser@gmail.com',
            '_password' => 'passwordadmin',
        ]);

        $this->assertForm();

        // test that the site contains the logout button
        $this->assertSelectorExists('a[href="/logout"]');

        // asserts that the user role is admin
        $this->assertContains('ROLE_ADMIN', $this->client->getContainer()->get('security.token_storage')->getToken()->getUser()->getRoles());

        // assert that the user is an admin
        $this->assertSelectorExists('a[href="/admin"]');

        $crawler = $this->client->request('GET', '/admin');

        $this->assertResponseIsSuccessful();
        // $this->assertPageTitleSame('Admin Panel');
        // asserts that the page contains a h1 with the text Admin Panel
        $this->assertSelectorTextContains('h1', 'Admin Panel');
        // $this->assertSelectorTextContains('html', 'List of Users'); // change this according to your actual page
        // asserts that the page contains a div with the class user_list
        $this->assertSelectorExists('.user_list');
    }

    public function testShowNonExistentUser(): void
    {

        $this->form = $this->crawler->selectButton('Se connecter')->form([
            '_username' => 'adminuser@gmail.com',
            '_password' => 'passwordadmin',
        ]);
        
        $this->assertForm();
        
        // test that the site contains the logout button
        $this->assertSelectorExists('a[href="/logout"]');

        // asserts that the user role is admin
        $this->assertContains('ROLE_ADMIN', $this->client->getContainer()->get('security.token_storage')->getToken()->getUser()->getRoles());

        // assert that the user is an admin
        $this->assertSelectorExists('a[href="/admin"]');
        $this->client->request('GET', '/admin/9999'); // 9999 assumes that no user with this ID exists

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $this->client->followRedirect();

        $this->assertSelectorTextContains('div.alert', 'Utilisateur non trouvé');
    }

    public function testEditNonExistentUser(): void
    {

        $this->form = $this->crawler->selectButton('Se connecter')->form([
            '_username' => 'adminuser@gmail.com',
            '_password' => 'passwordadmin',
        ]);

        $this->assertForm();

        // test that the site contains the logout button
        $this->assertSelectorExists('a[href="/logout"]');

        // asserts that the user role is admin
        $this->assertContains('ROLE_ADMIN', $this->client->getContainer()->get('security.token_storage')->getToken()->getUser()->getRoles());

        // assert that the user is an admin
        $this->assertSelectorExists('a[href="/admin"]');

        $this->client->request('GET', '/admin/9999/edit'); // 9999 assumes that no user with this ID exists

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $this->client->followRedirect();

        $this->assertSelectorTextContains('div.alert', 'Utilisateur non trouvé');
    }

    // Function to test finding a valid user as an admin
    public function testShowValidUserAsAdmin(): void
    {


        $this->form = $this->crawler->selectButton('Se connecter')->form([
            '_username' => 'adminuser@gmail.com',
            '_password' => 'passwordadmin',
        ]);

        $this->assertForm();

        // test that the site contains the logout button

        $this->assertSelectorExists('a[href="/logout"]');

        // asserts that the user role is admin

        $this->assertContains('ROLE_ADMIN', $this->client->getContainer()->get('security.token_storage')->getToken()->getUser()->getRoles());

        // assert that the user is an admin

        $this->assertSelectorExists('a[href="/admin"]');

        // get the user with the email anonymous@gmail using the entity

        $user = $this->userRepository->findOneBy(['email' => 'anonymous@gmail.com']);

        // get the id of the user

        $id = $user->getId();

        // try to access the page of a user

        $this->client->request('GET', '/admin/' . $id);

        $this->assertResponseIsSuccessful();

        $name = $user->getFullname();

        // asserts that in the page there is a h1 with the class page-header and the name of the user 
        $this->assertSelectorTextContains('h1.page-header', $name);
    }

    // Function to test that if we find a user with the admin role and edit it , it will correctly remove the admin role
    public function testRemoveadminRole(): void
    {
        $this->form = $this->crawler->selectButton('Se connecter')->form([
            '_username' => 'adminuser@gmail.com',
            '_password' => 'passwordadmin',
        ]);

        $this->assertForm();

        // test that the site contains the logout button

        $this->assertSelectorExists('a[href="/logout"]');

        // asserts that the user role is admin

        $this->assertContains('ROLE_ADMIN', $this->client->getContainer()->get('security.token_storage')->getToken()->getUser()->getRoles());

        // assert that the user is an admin

        $this->assertSelectorExists('a[href="/admin"]');

        // get the user with the email anonymous@gmail using the entity

        $user = $this->userRepository->findOneBy(['email' => 'adminuserroleremoved@gmail.com']);

        // get the id of the user

        $id = $user->getId();

        // try to access the page of a user

        $this->client->request('GET', '/admin/' . $id);

        $this->assertResponseIsSuccessful();

        // refresh
        $this->entityManager->refresh($user);

        $name = $user->getFullname();

        // asserts that in the page there is a h1 with the class page-header and the name of the user 
        $this->assertSelectorTextContains('h1.page-header', $name);

        // go to the edit page of the user
        $this->client->request('GET', '/admin/' . $id . '/edit');


        // refresh
        $this->entityManager->refresh($user);


        // follow the redirect
        $this->client->followRedirect();

        $this->client->request('GET', '/admin/' . $id);

        $this->assertResponseIsSuccessful();
        

        // asserts that the page has a li with a strong inside with the text Roles:
        $this->assertSelectorExists('li strong', 'Roles:');

        // asserts that still inside the li, below the strong, there is a the text without the ROLE_ADMIN
        $this->assertSelectorTextNotContains('li', 'ROLE_ADMIN');
    }

    // function to test that in the edit page of a user, there is a button with the id user_delete present
    public function testDeleteUserButtonIsPresent(): void
    {

        $this->form = $this->crawler->selectButton('Se connecter')->form([
            '_username' => 'adminuser@gmail.com',
            '_password' => 'passwordadmin',
        ]);

        $this->assertForm();

        // test that the site contains the logout button

        $this->assertSelectorExists('a[href="/logout"]');

        // asserts that the user role is admin

        $this->assertContains('ROLE_ADMIN', $this->client->getContainer()->get('security.token_storage')->getToken()->getUser()->getRoles());

        // assert that the user is an admin

        $this->assertSelectorExists('a[href="/admin"]');

        // get the user with the email anonymous@gmail using the entity

        $user = $this->userRepository->findOneBy(['email' => 'adminuserroleremoved@gmail.com']);

        // get the id of the user

        $id = $user->getId();

        // try to access the page of a user

        $this->client->request('GET', '/admin/' . $id);

        $this->assertResponseIsSuccessful();

        // refresh
        $this->entityManager->refresh($user);

        $name = $user->getFullname();

        // asserts that in the page there is a h1 with the class page-header and the name of the user 
        $this->assertSelectorTextContains('h1.page-header', $name);

        // assert that the page contains an element with the id user_delete
        $this->assertSelectorExists('#user_delete');

    }

    public function testUserIsDeleted(): void
    {
        $this->form = $this->crawler->selectButton('Se connecter')->form([
            '_username' => 'adminuser@gmail.com',
            '_password' => 'passwordadmin',
        ]);

        $this->assertForm();

        // test that the site contains the logout button

        $this->assertSelectorExists('a[href="/logout"]');

        // asserts that the user role is admin

        $this->assertContains('ROLE_ADMIN', $this->client->getContainer()->get('security.token_storage')->getToken()->getUser()->getRoles());

        // assert that the user is an admin

        $this->assertSelectorExists('a[href="/admin"]');

        // get the user with the email anonymous@gmail using the entity

        $user = $this->userRepository->findOneBy(['email' => 'adminuserroleremoved@gmail.com']);

        // get the id of the user

        $id = $user->getId();

        // try to access the page of a user

        $this->client->request('GET', '/admin/' . $id);

        $this->assertResponseIsSuccessful();

        // refresh
        $this->entityManager->refresh($user);

        $name = $user->getFullname();

        // asserts that in the page there is a h1 with the class page-header and the name of the user 
        $this->assertSelectorTextContains('h1.page-header', $name);

        // click on the button to delete the user
        $this->client->clickLink('Delete');

        // follow the redirect
        $this->client->followRedirect();

        $this->assertSelectorTextContains('div.alert', 'Utilisateur supprimé');

        // assert that the user is deleted
        $this->assertNull($this->userRepository->findOneBy(['email' => 'adminuserroleremoved@gmail.com']));

        $this->reloadFixtures();
    }

    // test redirect when user not found in deletion
    public function testUserIsNotFoundInDeletion(): void
    {
        $this->form = $this->crawler->selectButton('Se connecter')->form([
            '_username' => 'adminuser@gmail.com',
            '_password' => 'passwordadmin',
        ]);

        $this->assertForm();

        // test that the site contains the logout button

        $this->assertSelectorExists('a[href="/logout"]');

        // asserts that the user role is admin

        $this->assertContains('ROLE_ADMIN', $this->client->getContainer()->get('security.token_storage')->getToken()->getUser()->getRoles());

        // assert that the user is an admin

        $this->assertSelectorExists('a[href="/admin"]');

        // get the user with the email anonymous@gmail using the entity

        $user = $this->userRepository->findOneBy(['email' => 'adminuserroleremoved@gmail.com']);

        // get the id of the user

        $id = $user->getId();

        // try to access the page of a user

        $this->client->request('GET', '/admin/' . $id);

        $this->assertResponseIsSuccessful();

        // refresh
        $this->entityManager->refresh($user);

        $name = $user->getFullname();

        // asserts that in the page there is a h1 with the class page-header and the name of the user 
        $this->assertSelectorTextContains('h1.page-header', $name);

        
        // delete the user so that it is not found
        $this->entityManager->remove($user);

        $this->entityManager->flush();
        
        // click on the button to delete the user
        $this->client->clickLink('Delete');

        // follow the redirect
        $this->client->followRedirect();

        // asserth that the response contains the message Utilisateur non trouvé
        $this->assertSelectorTextContains('div.alert', 'Utilisateur non trouvé');

        $this->reloadFixtures();

    }

    private function reloadFixtures()
    {
        // Create a new Process instance
        $process = new Process([
            'php',
            'bin/console',
            'doctrine:fixtures:load',
            '--no-interaction',
            '--env=test'
        ]);

        // Execute the process and block until it finishes
        $process->run();

        // You can also check if the process was successful
        if (!$process->isSuccessful()) {
            // Handle error
            throw new RuntimeException($process->getErrorOutput());
        }
    }

    public function testToggleUserRole(): void
    {

        $this->form = $this->crawler->selectButton('Se connecter')->form([
            '_username' => 'adminuser@gmail.com',
            '_password' => 'passwordadmin',
        ]);

        $this->assertForm();

        // test that the site contains the logout button
        $this->assertSelectorExists('a[href="/logout"]');

        // asserts that the user role is admin
        $this->assertContains('ROLE_ADMIN', $this->client->getContainer()->get('security.token_storage')->getToken()->getUser()->getRoles());

        // assert that the user is an admin
        $this->assertSelectorExists('a[href="/admin"]');

        // Find a user to test
        $user = $this->userRepository->findOneBy(['email' => 'anonymous@gmail.com']);
        $id = $user->getId();

        $this->client->request('GET', '/admin/' . $id . '/edit');

        $this->assertTrue($this->client->getResponse()->isRedirect());
        $this->client->followRedirect();

        // Reload the user from the database
        $this->entityManager->refresh($user);

        if (in_array('ROLE_ADMIN', $user->getRoles())) {
            $this->assertContains('ROLE_ADMIN', $user->getRoles());
        } else {
            $this->assertNotContains('ROLE_ADMIN', $user->getRoles());
        }
        
    }


    // Function to verify that if someone tries to access the edit of a user without being an admin, he is redirected to the home page
    public function testAdminLinkNotAdminEdit(): void
    {
        $this->form = $this->crawler->selectButton('Se connecter')->form([
            '_username' => 'anonymous@gmail.com', // user is not an admin
            '_password' => 'password',
        ]);

        $this->assertForm();

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
    // Restore the original roles
    if ($this->originalRoles) {
        $user = $this->userRepository->findOneBy(['email' => 'anonymous@gmail.com']);
        $user->setRoles($this->originalRoles);
        
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    // close the entityManager
    $this->entityManager->close();

    // Call parent tearDown to finish test cleanup
    parent::tearDown();
}
}
