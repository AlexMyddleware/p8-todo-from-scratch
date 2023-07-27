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

    // functions with name task_edit, task_toggle, task_delete, task_create

    #[Route('/task/{id}/edit', name: 'task_edit')]
    public function task_edit(int $id): Response
    {
        $task = $this->taskRepository->find($id);
        return $this->render('task/edit.html.twig', ['task' => $task]);
    }

    #[Route('/task/{id}/toggle', name: 'task_toggle')]
    public function task_toggle(int $id): Response
    {
        $task = $this->taskRepository->find($id);
        $task->toggle(!$task->getIsDone());
        $this->taskRepository->save($task, true);
        return $this->redirectToRoute('task_list');
    }

    #[Route('/task/{id}/delete', name: 'task_delete')]
    public function task_delete(int $id): Response
    {
        $task = $this->taskRepository->find($id);
        $this->taskRepository->delete($task);
        return $this->redirectToRoute('task_list');
    }

    #[Route('/task/create', name: 'task_create')]
    public function task_create(): Response
    {
        return $this->render('task/create.html.twig');
    }
}
