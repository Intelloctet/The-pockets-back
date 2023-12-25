<?php

namespace App\Controller\User;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\SerializerInterface;

class ApiListAllUsersController extends AbstractController
{
    private $manager;
    private $user;
    private $passwordHasher;
    private $regManager;
    private $serializer;

    public function __construct(
        EntityManagerInterface $em,
        UserRepository $user,
        ManagerRegistry $doctrine,
        SerializerInterface $serializer
    ) {
        $this->manager = $em;
        $this->user = $user;
        $this->regManager = $doctrine;
        $this->serializer = $serializer;
    }
    #[Route('/api/v1/allUsers', name: 'app_user_api_list_all_users', methods: ['GET']),]
    public function index(): JsonResponse
    {
        try {
            $users = $this->user->findAll();
            //Using Serialization Groups Attributes
            $context = (new ObjectNormalizerContextBuilder())
                ->withGroups('getUser')
                ->toArray();
            $listUserSerialized = $this->serializer->serialize($users, 'json', $context);
            return $this->json(
                [
                    'status' => 'success',
                    'data' => $listUserSerialized
                ],
                Response::HTTP_OK
            );
        } catch (\Exception $ex) {
            return $this->json(
                [
                    'status' => 'error',
                    'errorMsg' =>  $ex->getMessage(),
                    'message' => "Server Error"
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
