<?php

namespace App\Controller;

use App\Repository\TaskRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TaskController extends AbstractController
{

    private TaskRepository $taskRepository;

    // cosntructor that includes the repository for task
    public function __construct(TaskRepository $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    #[Route('/task', name: 'task_list')]
    public function getAllTasks(): Response
    {
        $tasks = $this->taskRepository->findAll();
        return $this->render('task/list.html.twig', ['tasks' => $tasks]);
    }
}
