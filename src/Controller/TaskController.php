<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TaskController extends AbstractController
{
    #[Route('/task', name: 'task_list')]
    public function getAllTasks(): Response
    {
        $tasks = [];
        return $this->render('task/list.html.twig', ['tasks' => $tasks]);
    }
}
