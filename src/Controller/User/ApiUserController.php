<?php

namespace App\Controller\User;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\SerializerInterface;

class ApiUserController extends AbstractController
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

    #[Route('/api/v1/user', name: 'app_user_api_user', methods: ['POST'])]
    public function index(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            if(!$data['username'])
                throw new Exception("Error Processing Request , username null or undefined", 1);
                
            $user = $this->user->findOneBy(['username' => $data['username']]);
            //Using Serialization Groups Attributes
            $context = (new ObjectNormalizerContextBuilder())
                ->withGroups('getUser')
                ->toArray();
            $userSerialized = $this->serializer->serialize($user, 'json', $context);

            return $this->json(
                [
                    'status' => 'success',
                    'data' => $userSerialized
                ],
                Response::HTTP_OK
            );
        } catch (\Exception $ex) {
            return $this->json(
                [
                    'status' => 'error',
                    'errorMsg' => $ex->getMessage(),
                    'message' => "Server Error"
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
