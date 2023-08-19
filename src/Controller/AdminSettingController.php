<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
// use the user repository
use App\Repository\UserRepository;

class AdminSettingController extends AbstractController
{
    private UserRepository $userRepository;
    // constructor that includes the repository for user
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    // function to to show the admin pannel, wihch lists all the users, only available for the admin
    #[Route('/admin', name: 'admin_users', methods: ['GET'])]
    public function index(): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            // add flash message
            $this->addFlash('danger', 'Vous devez être connecté en tant qu\'administrateur pour accéder à cette page');
            return $this->redirectToRoute('app_home');
        }

        // get all the user using the getAllUsers function from the user repository
        $users = $this->userRepository->getAllUsers();
        return $this->render('admin/panel.html.twig', ['users' => $users]);
    }

    // function to show a single user
    #[Route('/admin/{id}', name: 'user_show', methods: ['GET'])]
    public function show(int $id): Response
    {

        if (!$this->isGranted('ROLE_ADMIN')) {
            // add flash message
            $this->addFlash('danger', 'Vous devez être connecté en tant qu\'administrateur pour accéder à cette page');
            return $this->redirectToRoute('app_home');
        }

        $user = $this->userRepository->getUserById($id);

        // if the user is not found
        if (!$user) {
            // add flash message
            $this->addFlash('danger', 'Utilisateur non trouvé');
            return $this->redirectToRoute('admin_users');
        }

        // get the user using the getUserById function from the user repository
        return $this->render('admin/user.html.twig', ['user' => $user]);
    }

    // Function to edit the roles of a user, switching between admin and user
    #[Route('/admin/{id}/edit', name: 'user_edit', methods: ['GET', 'POST'])]
    public function edit(int $id): Response
    {

        if (!$this->isGranted('ROLE_ADMIN')) {
            // add flash message
            $this->addFlash('danger', 'Vous devez être connecté en tant qu\'administrateur pour accéder à cette page');
            return $this->redirectToRoute('app_home');
        }

        $user = $this->userRepository->getUserById($id);

         // if the user is not found
         if (!$user) {
            // add flash message
            $this->addFlash('danger', 'Utilisateur non trouvé');
            return $this->redirectToRoute('admin_users');
        }
        
        // get the user using the getUserById function from the user repository
        $roles = $user->getRoles();
        // if the user is an admin, remove the admin role
        if (in_array('ROLE_ADMIN', $roles)) {
            $user->removeRole('ROLE_ADMIN');
        } else {
            // if the user is not an admin, add the admin role
            $user->addRole('ROLE_ADMIN');
        }
        // save the user
        $this->userRepository->save($user, true);
        // redirect to the admin panel
        return $this->redirectToRoute('admin_users');
    }
}
