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
    name: 'Anonymous-tasks',
    description: 'Add a short description for your command',
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

        // Fetch user with id 1
        $user = $this->userRepository->find(1);

        // If the user doesn't exist, display an error message
        if (!$user) {
            $io->error('No user found with ID 1');
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
