<?php

// class to test the anonymous tasks command
namespace App\Tests\Command;


use App\Command\AnonymousTasksCommand;

use Symfony\Bundle\FrameworkBundle\Console\Application;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

use Symfony\Component\Console\Tester\CommandTester;

class AnonymousTasksCommandTest extends KernelTestCase
{
    public function testExecute()
    {
        // Create a new kernel
        $kernel = static::createKernel();

        // Boot the kernel
        $kernel->boot();

        // Get the application
        $application = new Application($kernel);

        // Get the command
        $command = $application->find('anonymous-tasks');

        // Create a new command tester
        $commandTester = new CommandTester($command);

        // Execute the command
        $commandTester->execute([
            'command' => $command->getName(),
        ]);

        // Get the output
        $output = $commandTester->getDisplay();

        // Assert that the output contains the following
        $this->assertStringContainsString('All tasks without authors have been updated.', $output);
    }

    public function testExecuteWithNoUser()
    {
        // Create a new kernel
        $kernel = static::createKernel();

        // Boot the kernel
        $kernel->boot();

        // Get the application
        $application = new Application($kernel);

        // Get the command
        $command = $application->find('anonymous-tasks');

        // Create a new command tester
        $commandTester = new CommandTester($command);

        // change the email of the user, replace anonymous@gmail.com with nouser@gmail.com by using a direct request to the test database
        $fullname = 'John Doe';  // replace this with the actual fullname of the user who has the email anonymous@gmail.com
        $kernel->getContainer()->get('doctrine')->getConnection()->executeQuery('UPDATE user SET email = ? WHERE fullname = ?', ['nouser@gmail.com', $fullname]);

        // Execute the command
        $commandTester->execute([
            'command' => $command->getName(),
        ]);

        // Get the output
        $output = $commandTester->getDisplay();

        // Assert that the output contains the following
        $this->assertStringContainsString('No user found with email anonymous@gmail.com', $output);

        // change the email of the user back to anonymous@gmail
        $kernel->getContainer()->get('doctrine')->getConnection()->executeQuery('UPDATE user SET email = ? WHERE fullname = ?', ['anonymous@gmail.com', $fullname]);

    }
}