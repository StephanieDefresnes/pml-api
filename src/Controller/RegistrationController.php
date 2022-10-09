<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;

class RegistrationController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private SerializerInterface $serializer,
    ){}
    
    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register( Request $request, UserPasswordHasherInterface $passwordHasher ): JsonResponse
    {
        $newUser = $this->serializer->deserialize($request->getContent(), User::class, 'json');
        $email = $newUser->getEmail();
        $plaintextPassword = $newUser->getPassword();
        
        if ( empty($email) || empty($plaintextPassword) ) {
            return $this->respondValidationError("Invalid Password or Email");
        }
        
        $user = new User();
        $user->setEmail($email);
        $hashedPassword = $passwordHasher->hashPassword(
            $user,
            $plaintextPassword
        );
        $user->setPassword($passwordHasher->hashPassword($user, $hashedPassword));
        $this->em->persist($user);
        $this->em->flush();
        
        return new JsonResponse('User successfully created', Response::HTTP_OK, [], true);
    }
}
