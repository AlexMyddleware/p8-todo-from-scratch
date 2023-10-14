<?php

namespace App\Command;

use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'anonymous-tasks',
    description: 'This is a command to update tasks without authors, and set the anonymous user as the author. His email is anonymous@gmail.com',
)]
class AnonymousTasksCommand extends Command
{

    private $taskRepository;
    private $userRepository;

    public function __construct(TaskRepository $taskRepository, UserRepository $userRepository)
    {
        $this->taskRepository = $taskRepository;
        $this->userRepository = $userRepository;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $user = $this->userRepository->findOneByEmail('anonymous@gmail.com');

        // If the user doesn't exist, display an error message
        if (!$user) {
            $io->error('No user found with email anonymous@gmail.com');
            return Command::FAILURE;
        }

        // Fetch tasks with no author
        $tasks = $this->taskRepository->findBy(['created_by' => null]);

        // Iterate through the tasks and set the user as the author
        foreach ($tasks as $task) {
            $task->setCreatedBy($user);
            $this->taskRepository->save($task, true);
        }

        $io->success('All tasks without authors have been updated.');

        return Command::SUCCESS;
    }
}
