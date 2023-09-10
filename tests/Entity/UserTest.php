<?php

namespace App\Tests\Entity;

use App\Entity\Task;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testIsVerified()
    {
        $user = new User();
        $user->setIsVerified(true);
        
        $this->assertTrue($user->isVerified());
    }

    public function testAddTask()
    {
        $user = new User();
        $task = new Task();

        $user->addTask($task);

        $this->assertCount(1, $user->getTasks());
        $this->assertSame($task->getCreatedBy(), $user);
    }

    public function testRemoveTask()
    {
        $user = new User();
        $task = new Task();

        // First, add a task
        $user->addTask($task);
        $this->assertCount(1, $user->getTasks());

        // Then, remove it
        $user->removeTask($task);
        $this->assertCount(0, $user->getTasks());
        $this->assertNull($task->getCreatedBy());
    }
}
