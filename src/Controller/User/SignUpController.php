<?php

namespace App\Controller\User;

use App\Entity\User;
use App\Entity\User\Profil;
use App\Enumarates\User\PassType;
use App\Services\Verify\IsStrongPassword;
use App\Services\Verify\IsValid;
use App\Services\String\Sanitized;
use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class SignUpController extends AbstractController
{
    #[Route('/api/signup', name: 'app_sign_up')]
    public function index(Request $request, UserPasswordHasherInterface $passwordHasher,ManagerRegistry $doctrine): JsonResponse
    {
        $now = new DateTime();
        $data = json_decode($request->getContent()); //Access attribut like object 
        $output = array(
            'data' => null,
            'message' => "Your content is empty",
            'status' => Response::HTTP_NO_CONTENT
        );
        $firstname = isset($data->firstname) ? Sanitized::stringValue($data->firstname) : null;
        $lastname = isset($data->lastname) ? Sanitized::stringValue($data->lastname) : null;
        $birthday = isset($data->birthday) ? Sanitized::stringValue($data->birthday) : null; //to handle : date format dd-mm-YYYY
        $country = isset($data->country) ? Sanitized::stringValue($data->country) : null;
        $username = isset($data->username) ? Sanitized::stringValue($data->username) : null;
        $password = isset($data->password) ? $data->password : null; //to handle
        $typeOfPassword = isset($data->typeOfPassword) ? Sanitized::stringValue($data->typeOfPassword) : null; //to handle
        $phone = isset($data->phone) ? Sanitized::stringValue($data->phone) : null; // to handle

        /** Check date format */
        try {
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
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY //Validation with  error
            ));

        /** Check phone validation */
        if ($phone) {
            if (!IsValid::phoneNumber($phone))
                return $this->json(array(
                    'message' => 'Invalid phone number',
                    'status' => Response::HTTP_UNPROCESSABLE_ENTITY //Validation with  error
                ));
        }

        /** Check password validation */
        if ($password) {

            if (!$typeOfPassword)
                return $this->json(array(
                    'message' => 'Password type cannot be empty',
                    'status' => Response::HTTP_UNPROCESSABLE_ENTITY //Validation with  error
                ));

            switch (strtolower($typeOfPassword)) {
                case PassType::PIN: {
                        if (!IsStrongPassword::pin($password))
                            return $this->json(array(
                                'data' => $password,
                                'message' => 'Invalid PIN code! [it\'s should be numbers and length equal 4]',
                                'status' => Response::HTTP_UNPROCESSABLE_ENTITY //Validation with  error
                            ));
                    }
                    break;
                case PassType::LOW: {
                        if (!IsStrongPassword::low($password))
                            return $this->json(array(
                                'data' => $password,
                                'message' => 'Invalid password code! [it\'s length should be 8 at least]',
                                'status' => Response::HTTP_UNPROCESSABLE_ENTITY //Validation with  error
                            ));
                    }
                    break;

                default: {
                    }
                    break;
            }
        } else {
            return $this->json(array(
                'message' => 'Password cannot be empty or null',
                'status' => Response::HTTP_UNPROCESSABLE_ENTITY //Validation with  error
            ));
        }

        $user = new User();
        $user->setUsername($username)->setPhone($phone)
            ->setIsBlocked(false)->setIsDeleted(false)
            ->setTypeOfPassword($typeOfPassword)
            ->setCreatedAt($now);

        $profil = new Profil();
        $profil->setFirstname($firstname)->setLastname($lastname)
            ->setCountry($country)->setBirthday($birthday)
            ->setCreateAt($now)
            ->setIsDeleted(false);

        $user->setProfil($profil)
            ->setPassword($passwordHasher->hashPassword($user, $password));

        try {
            $em = $doctrine->getManager('customer');
            $em->persist($user);
            $em->flush();
            $output = array(
                'message' => "User created succefully!",
                'status' => Response::HTTP_CREATED
            );
        } catch (\Throwable $th) {
            $output = array(
                'data' => $user,
                'message' => "Error when creating user : ".$th,
                'status' => Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
        return $this->json($output);
    }
}
