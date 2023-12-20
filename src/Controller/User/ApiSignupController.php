<?php

namespace App\Controller\User;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Services\String\Sanitized;
use App\Services\Verify\IsValid;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class ApiSignupController extends AbstractController
{
    private $manager;
    private $user;
    private $passwordHasher;
    private $regManager;

    public function __construct(EntityManagerInterface $em, UserRepository $user, UserPasswordHasherInterface $passwordHasher, ManagerRegistry $doctrine)
    {
        $this->manager = $em;
        $this->user = $user;
        $this->passwordHasher = $passwordHasher;
        $this->regManager = $doctrine;
    }

    //Create user identification
    #[Route('/v1/signup', name: 'app_user_api_signup')]
    public function index(Request $request): JsonResponse
    {
        $now = new DateTime();
        $data = json_decode($request->getContent()); // get request as object

        $username = isset($data->username) ? Sanitized::stringValue($data->username) : null;
        $password = isset($data->password) ? $data->password : null; //to handle
        $phone = isset($data->phone) ? Sanitized::stringValue($data->phone) : null; // to handle

        /** Check username validation */
        if (!$username)
            return $this->json(array(
                'message' => 'Username cannot be empty',
                'errorMsg' => Response::HTTP_UNPROCESSABLE_ENTITY ,//Validation with  error,
                'status' => 'error'
            ));

        $user_exist = $this->user->findOneByUsername($username);

        if ($user_exist)
            return $this->json(array(
                'message' => 'Username already exist! Choose other!',
                'errorMsg' => Response::HTTP_UNPROCESSABLE_ENTITY ,//Validation with  error,
                'status' => 'error'
            ));

        /** Check phone validation */
        if (!$password) {
            return $this->json(array(
                'message' => 'Invalid password',
                'errorMsg' => Response::HTTP_UNPROCESSABLE_ENTITY, //Validation with  error
                'status' => 'error'
            ));
        }
        /** Check phone validation */
        if ($phone) {
            if (!IsValid::phoneNumber($phone))
                return $this->json(array(
                    'message' => 'Invalid phone number',
                    'errorMsg' => Response::HTTP_UNPROCESSABLE_ENTITY, //Validation with  error
                    'status' => 'error'
                ));
        }

        try {
            
            $user = new User();
            $hashPassword = $this->passwordHasher->hashPassword($user,$password);
            $user->setUsername($username)->setPassword($hashPassword)->setPhone($phone)
                 ->setCreatedAt($now)->setUpdatedAt(null);
            $em = $this->regManager->getManager('customer');
            $em->persist($user);
            $em->flush();

            return new JsonResponse([
                'message' => 'User create successfully!',
                'status' => 'success'
            ]);
        } catch (\Exception $ex) {
            return new JsonResponse([
                'message' => 'Cannot create user!',
                'status' => 'error',
                'errorMsg' => $ex
            ]);
        }
    }
}
