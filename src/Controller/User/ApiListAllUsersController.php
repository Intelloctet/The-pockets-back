<?php

namespace App\Controller\User;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ApiListAllUsersController extends AbstractController
{
    private $manager;
    private $user;
    private $passwordHasher;
    private $regManager;

    public function __construct(EntityManagerInterface $em, UserRepository $user, ManagerRegistry $doctrine)
    {
        $this->manager = $em;
        $this->user = $user;
        $this->regManager = $doctrine;
    }
    #[Route('/api/allUsers', name: 'app_user_api_list_all_users')]
    public function index(): JsonResponse
    {
        $users = $this->user->findAll();
        return $this->json([
            'status' => 'success',
            'data' => $users,
        ]);
    }
}
