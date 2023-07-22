<?php

namespace App\Controller;

use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\ArrayAdapter;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class HomeController extends AbstractController
{


    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function home(Request $request, EntityManagerInterface $entityManager): Response
    {


        return $this->render('default/index.html.twig', [
            // 'pagerfanta' => $pagerfanta,
        ]);
    }

    
}
