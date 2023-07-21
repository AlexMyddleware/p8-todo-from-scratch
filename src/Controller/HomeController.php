<?php

namespace App\Controller;

use Pagerfanta\Pagerfanta;
use App\Repository\TrickRepository;
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
    public function home(Request $request, EntityManagerInterface $entityManager, TrickRepository $trickRepository): Response
    {

        // create a query builder using the query builder of the repository
        $queryBuilder = $trickRepository->getAllTricksQueryBuilder();
        // create a new adapter to paginate the trick entities using the query builder
        $adapter = new QueryAdapter($queryBuilder);
        // create a new pagerfanta instance with the adapter and the current page set to 1 and the max number of tricks per page set to 5
        $pagerfanta = Pagerfanta::createForCurrentPageWithMaxPerPage(
            $adapter,
            $request->query->get('page', 1),
            20
        );


        return $this->render('home.html.twig', [
            'pagerfanta' => $pagerfanta,
        ]);
    }

    
}
