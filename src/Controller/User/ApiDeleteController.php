<?php

namespace App\Controller\User;

use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class ApiDeleteController extends AbstractController
{
    private $repoUser;
    private $serializer;
    private $em;
    function __construct(
        UserRepository $repoUser,
        SerializerInterface $serializerInterface,
        EntityManagerInterface $em,
    ) {
        $this->repoUser = $repoUser;
        $this->serializer = $serializerInterface;
        $this->em = $em;
    }

    #[Route('/api/v1/user/delete', name: 'app_user_delete')]
    public function index(Request $request): JsonResponse
    {
        $now = new DateTime();
        $data = json_decode($request->getContent(), true);
        try {
            $getUser = $this->repoUser->findOneByUsername($data['username']);
            $getUser->setIsDeleted(!$getUser->isIsDeleted())->setUpdatedAt($now);
            $this->em->persist($getUser);
            $this->em->flush();
            if ($getUser->isIsDeleted())
                return $this->json([
                    'message' => 'User deleted successfully!',
                    'status' => 'success',
                ]);
            return $this->json([
                'message' => 'User restored successfully!',
                'status' => 'success',
            ]);
        } catch (\Exception $ex) {
            return $this->json(array(
                'errorMsg' =>  $ex->getMessage(),
                'message' => 'Request failed!',
                'status' => 'error'
            ), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
