<?php

namespace App\Controller\User;

use App\Entity\Profil;
use App\Entity\User;
use App\Enumeration\User\PassType;
use App\Repository\UserRepository;
use App\Services\String\Sanitized;
use App\Services\Verify\IsStrongPassword;
use App\Services\Verify\IsValid;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
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
        $typeOfPassword = isset($data->typeOfPassword) ? Sanitized::stringValue($data->typeOfPassword) : null; //to handle
        $password = isset($data->password) ? $data->password : null; //to handle
        $phone = isset($data->phone) ? Sanitized::stringValue($data->phone) : null; // to handle

        $firstname = isset($data->firstname) ? Sanitized::stringValue($data->firstname) : null;
        $lastname = isset($data->lastname) ? Sanitized::stringValue($data->lastname) : null;
        $birthday = isset($data->birthday) ? Sanitized::stringValue($data->birthday) : null; //to handle : date format dd-mm-YYYY
        $country = isset($data->country) ? Sanitized::stringValue($data->country) : null;

        /** Check date format */
        try {
            if ($birthday)
                $birthday = new DateTime($data->birthday); //to handle : date format dd-mm-YYYY
        } catch (Exception $ex) {
            return $this->json(array(
                'message' => 'Invalid date format! Try like this dd-mm-yyyy',
                'status' => Response::HTTP_BAD_REQUEST
            ));
        }
        /** Check username validation */
        if (!$username)
            return $this->json(array(
                'message' => 'Username cannot be empty',
                'errorMsg' => Response::HTTP_UNPROCESSABLE_ENTITY, //Validation with  error,
                'status' => 'error'
            ));

        $user_exist = $this->user->findOneByUsername($username);

        if ($user_exist)
            return $this->json(array(
                'message' => 'Username already exist! Choose other!',
                'errorMsg' => Response::HTTP_UNPROCESSABLE_ENTITY, //Validation with  error,
                'status' => 'error'
            ));

        /** Check password validation */
        if ($password) {
            if (!$typeOfPassword)
                return $this->json(array(
                    'message' => 'Password type cannot be empty',
                    'status' => 'error'
                ),Response::HTTP_BAD_REQUEST);

            switch (strtolower($typeOfPassword)) {
                case PassType::PIN: {
                        if (!IsStrongPassword::pin($password))
                            return $this->json(array(
                                'message' => 'Invalid PIN code! [it\'s should be numbers and length equal 4]',
                                'status' => 'error'
                            ),Response::HTTP_BAD_REQUEST);
                    }
                    break;
                case PassType::LOW: {
                        if (!IsStrongPassword::low($password))
                            return $this->json(array(
                                'message' => 'Invalid password code! [it\'s length should be 8 at least]',
                                'status' => 'error'
                            ),Response::HTTP_BAD_REQUEST);
                    }
                    break;

                default: {
                    }
                    break;
            }
        } else {
            return $this->json(array(
                'message' => 'Password cannot be empty or null',
                'status' => 'error'
            ),Response::HTTP_BAD_REQUEST);
        }
        /** Check phone validation */
        if ($phone) {
            if (!IsValid::phoneNumber($phone))
                return $this->json(array(
                    'message' => 'Invalid phone number',
                    'status' => 'error'
                ),Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {

            $user = new User();
            $profil = new Profil();
            $profil->setFirstname($firstname)->setLastname($lastname)->setCountry($country)->setBirthday($birthday)
            ->setIsDeleted(false)
            ->setCreatedAt($now)->setUpdatedAt(null);

            $hashPassword = $this->passwordHasher->hashPassword($user, $password);
            
            $user->setUsername($username)->setPassword($hashPassword)->setPhone($phone)->setProfil($profil)
                ->setIsBlocked(false)->setIsDeleted(false)->setTypeOfPassword($typeOfPassword)
                ->setCreatedAt($now)->setUpdatedAt(null);

           

            $em = $this->regManager->getManager('customer');
            $em->persist($user);
            $em->flush();
           

            return new JsonResponse([
                'message' => 'User create successfully!',
                'status' => 'success'
            ]);
        } catch (Exception $ex) {
            return new JsonResponse([
                'message' => 'Cannot create user!',
                'status' => 'error',
                'errorMsg' => $ex
            ],500);
        }
    }
}
