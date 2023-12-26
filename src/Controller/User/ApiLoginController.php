<?php

namespace App\Controller\User;

use App\Entity\User\User;
use App\Repository\UserRepository;
use App\Services\String\Sanitized;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * TO DO: Verify if user token is valid before create new one or disable the last token and create new
 */
class ApiLoginController extends AbstractController
{
    private $manager;
    private $user;
    private $passwordHasher;
    private $regManager;
    private $jwtManager;

    public function __construct(
        EntityManagerInterface $em,
        UserRepository $user,
        UserPasswordHasherInterface $passwordHasher,
        ManagerRegistry $managerReg,
        JWTTokenManagerInterface $jwtManager,
    ) {
        $this->manager = $em;
        $this->user = $user;
        $this->passwordHasher = $passwordHasher;
        $this->regManager = $managerReg;
        $this->jwtManager = $jwtManager;
    }

    #[Route('/signin', name: 'app_user_api_login')]
    public function index(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent());
        $username = isset($data->username) ? Sanitized::stringValue($data->username) : null;
        $password = isset($data->password) ? $data->password : null; //to handle
        $phone = isset($data->phone) ? Sanitized::stringValue($data->phone) : null; // to handle

        /** Check username validation */
        if (!$username)
            return $this->json(array(
                'message' => 'Username cannot be empty',
                'status' => 'error'
            ), Response::HTTP_UNPROCESSABLE_ENTITY);

        try {
            $user_exist = $this->user->findOneByUsername($username);
            if (!$user_exist)
                return $this->json(array(
                    'message' => 'Username not recognized!',
                    'status' => 'error'
                ), Response::HTTP_UNPROCESSABLE_ENTITY);

            if ($user_exist->isIsBlocked())
                return $this->json(array(
                    'message' => 'This username was blocked!',
                    'status' => 'error'
                ), Response::HTTP_UNPROCESSABLE_ENTITY);

            if ($user_exist->isIsDeleted())
                return $this->json(array(
                    'message' => 'This username was deleted!',
                    'status' => 'error'
                ), Response::HTTP_UNPROCESSABLE_ENTITY);

            // Customize the payload (claims) of the JWT
            $payload = array(
                'user_id' => $user_exist->getId(),  // Adjust this based on your User entity
                'roles' => $user_exist->getRoles(), // Assuming your User entity has a getRoles method
                'phone' => $user_exist->getPhone(),
            );

            $token = $this->jwtManager->createFromPayload($user_exist, $payload);
        } catch (\Exception $ex) {
            return $this->json(array(
                'message' => 'Maybe database offline!',
                'status' => 'error'
            ), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        /** Check password validation */
        if (!$password) {
            return $this->json(array(
                'message' => 'Please check your password',
                'status' => 'error'
            ), Response::HTTP_UNPROCESSABLE_ENTITY);
        } else {

            $isPasswordValid = $this->passwordHasher->isPasswordValid($user_exist, $password); //ARG (Entity & Plaintext Pass)
            if (!$isPasswordValid) {
                return $this->json(array(
                    'message' => 'Password incorrect!',
                    'status' => 'error'
                ), Response::HTTP_BAD_REQUEST);
            }
        }


        return $this->json([
            'message' => 'Signin successfully!',
            'token' => $token,
        ], Response::HTTP_OK);
    }
}
