<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;

class UserController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
    ){}
    
    #[Route('/api/register', name: 'register', methods: ['POST'])]
    public function register( Request $request, UserPasswordEncoderInterface $encoder ): JsonResponse
    {
        $user = new User();
        $newUser = $this->serializer->deserialize($request->getContent(), User::class, 'json');
        $email = $newUser->getEmail();
        $password = $newUser->getPassword();
        
        if ( empty($password) || empty($email) ) {
            return $this->respondValidationError("Invalid Password or Email");
        }
        $user->setEmail($email);
        $user->setPassword($encoder->encodePassword($user, $password));
        $this->em->persist($user);
        $this->em-> flush();
        
        return $this->respondWithSuccess(sprintf('User %s successfully created', $user->getUsername()));
    }
}
