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

    private function checkVerified()
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $user = $this->getUser();
        if (!$user || !$user->isVerified()) {
            $this->addFlash('danger', 'Vous devez être vérifié pour accéder à cette page');
            return $this->redirectToRoute('app_home');
        }

        return null;
    }

    #[Route('/task', name: 'task_list')]
    public function getAllTasks(): Response
    {
        $redirectResponse = $this->checkVerified();
        if ($redirectResponse instanceof Response) {
            return $redirectResponse;
        }

        $tasks = $this->taskRepository->findAll();
        return $this->render('task/list.html.twig', ['tasks' => $tasks]);
    }

    // function to get all the completed tasks
    #[Route('/task/completed', name: 'task_completed')]
    public function getCompletedTasks(): Response
    {
        $redirectResponse = $this->checkVerified();
        if ($redirectResponse instanceof Response) {
            return $redirectResponse;
        }

        $tasks = $this->taskRepository->findBy(['isDone' => true]);
        return $this->render('task/list.html.twig', ['tasks' => $tasks]);
    }

    #[Route('/task/{id}/edit', name: 'task_edit')]
    public function task_edit(int $id, Request $request): Response
    {
        $redirectResponse = $this->checkVerified();
        if ($redirectResponse instanceof Response) {
            return $redirectResponse;
        }
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
        $redirectResponse = $this->checkVerified();
        if ($redirectResponse instanceof Response) {
            return $redirectResponse;
        }

        $task = $this->taskRepository->find($id);
        $task->toggle(!$task->getIsDone());
        $this->taskRepository->save($task, true);
        return $this->redirectToRoute('task_list');
    }

    #[Route('/task/{id}/delete', name: 'task_delete')]
    public function task_delete(int $id): Response
    {
        $redirectResponse = $this->checkVerified();
        if ($redirectResponse instanceof Response) {
            return $redirectResponse;
        }

        $task = $this->taskRepository->find($id);

        // get the task author
        $author = $task->getCreatedBy();

        // get the current logged in user
        $user = $this->getUser();
        
        // variable to say if the current user is an admin
        $isAdmin = $this->isGranted('ROLE_ADMIN');

        if ($author === null) {
            // add an error flash message
            $this->addFlash('error', 'You cannot delete a task that has no author');
            return $this->redirectToRoute('task_list');
        }
        
        // if the task author has an email anonymous@gmail.com and the $isAdmin is false, then you can't delete the task
        if ($author->getEmail() === 'anonymous@gmail.com' && !$isAdmin) {
            // add an error flash message
            $this->addFlash('error', 'You cannot delete an anonymous task because you are not an admin');
            return $this->redirectToRoute('task_list');
        }

        // if the user is not the author, we get an error. However, if the user is an admin and the author is anonymous, then the admin can delete the task
        if ($author !== $user && $author->getEmail() !== 'anonymous@gmail.com') {
            // add an error flash message
            $this->addFlash('error', 'You cannot delete a task that you did not create');
            return $this->redirectToRoute('task_list');
        }

        $this->taskRepository->remove($task, true);
        
        // add a success flash message
        $this->addFlash('success', 'The task has been deleted');
        
        return $this->redirectToRoute('task_list');
    }

    #[Route('/task/create', name: 'task_create')]
    public function task_create(Request $request): Response
    {
        $redirectResponse = $this->checkVerified();
        if ($redirectResponse instanceof Response) {
            return $redirectResponse;
        }

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
