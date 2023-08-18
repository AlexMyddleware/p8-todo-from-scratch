<?php

namespace App\Controller;

use App\Form\TaskType;
use App\Repository\TaskRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

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
    public function task_edit(int $id, Request $request): Response
    {
        // creates a form and a render much like in th task_create function
        $initialTask = $this->taskRepository->find($id);

        // if the task is not found, return a 404 error
        if (!$initialTask) {
            throw $this->createNotFoundException('The task does not exist');
        }

        $taskForm = $this->createForm(TaskType::class, $initialTask);

        $taskForm->handleRequest($request);
        if ($taskForm->isSubmitted() && $taskForm->isValid()) {
            $task = $taskForm->getData();
            $this->taskRepository->editTask($initialTask, $task->getTitle(), $task->getContent());
            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/edit.html.twig', ['taskForm' => $taskForm->createView(), 'task' => $initialTask]);

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
        $this->taskRepository->remove($task, true);
        return $this->redirectToRoute('task_list');
    }

    #[Route('/task/create', name: 'task_create')]
    public function task_create(Request $request): Response
    {
        // add form TaskType
        $taskForm = $this->createForm(TaskType::class);
        $taskForm->handleRequest($request);

        if ($taskForm->isSubmitted() && $taskForm->isValid()) {
            $task = $taskForm->getData();
            $user = $this->getUser();
            $this->taskRepository->createTask($task->getTitle(), $task->getContent(), $user);
            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/create.html.twig', ['taskForm' => $taskForm->createView()]);
    }
}
