<?php

namespace App\Controller\User;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SignInController extends AbstractController
{
    #[Route("/api/login", name: "app_sign_in", methods:["POST"])]
    public function signIn(Request $request, JWTTokenManagerInterface $jwt, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent());
        $username = isset($data->username) ? trim($data->username) : null;
        $password = isset($data->password) ? $data->password : null;

        if (!$username)
            return $this->json(array(
                "message" => "Username cannot be empty"
            ));
        if (!$password)
            return $this->json(array(
                "message" => "Password cannot be empty"
            ));

        $user = $em->getRepository(User::class)->findBy(['username' => $username, 'isBlocked' => false, 'isDeleted' => false]);
        var_dump($em);
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/User/SignInController.php',
        ]);
    }
}
