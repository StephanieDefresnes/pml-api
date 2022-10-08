<?php

namespace App\Controller;

use App\Entity\Post;
use App\Repository\PostRepository;
use App\Service\StatusObject;
use App\Service\CategoryObject;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;

class PostController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private PostRepository $postRepository,
        private SerializerInterface $serializer,
        private StatusObject $statusObject,
        private CategoryObject $categoryObject,
    ){}
    
    #[Route('/posts', name: 'getPosts', methods: ['GET'])]
    public function getPosts(): JsonResponse
    {
        $posts = $this->postRepository->findAll();
        $context = SerializationContext::create()->setGroups(['getPosts']);
        $jsonPosts = $this->serializer->serialize($posts, 'json', $context);
        return new JsonResponse($jsonPosts, Response::HTTP_OK, [], true);
    }

    #[Route('/posts/{id}', name: 'getPost', methods: ['GET'])]
    public function getPost( Post $post ): JsonResponse
    {
        $context = SerializationContext::create()->setGroups(['getPosts']);
        $jsonPost = $this->serializer->serialize($post, 'json', $context);
        return new JsonResponse($jsonPost, Response::HTTP_OK, [], true);
    }
    
    #[Route('/api/posts/{id}', name: 'deletePost', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: "Vous n'avez pas les droits suffisants pour supprimer un post")]
    public function deletePost( Post $post ): JsonResponse 
    {
        $this->em->remove($post);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
    
    #[Route('/api/posts/', name:"addPost", methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: "Vous n'avez pas les droits suffisants pour ajouter un post")]
    public function addPost( Request $request, UrlGeneratorInterface $urlGenerator ): JsonResponse 
    {
        $post = new Post();
        $newPost = $this->serializer->deserialize($request->getContent(), Post::class, 'json');
        $post->setType($newPost->getType());
        $post->setDateCreate(new \DateTime('now'));
        
        $content = $request->toArray();
        $post->setStatus( $this->statusObject->get( $content['status']  ) );
        $post->setCategory( $this->categoryObject->get( $content['category'] ) );
        
        $this->em->persist($post);
        $this->em->flush();

        $jsonPost = $this->serializer->serialize($post, 'json', ['groups' => 'getBooks']);
        
        $location = $urlGenerator->generate('getPost', ['id' => $post->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonPost, Response::HTTP_CREATED, ["Location" => $location], true);
    }
    
    
    #[Route('/api/posts/{id}', name:"updatePost", methods:['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: "Vous n'avez pas les droits suffisants pour modifier un post")]
    public function updatePost( Request $request, Post $post ): JsonResponse 
    {
        $newPost = $this->serializer->deserialize($request->getContent(), Post::class, 'json');
        $post->setType($newPost->getType());
        $post->setDateUpdate(new \DateTime('now'));
        
        $content = $request->toArray();
        $post->setStatus( $this->statusObject->get( $content['status']  ) );
        $post->setCategory( $this->categoryObject->get( $content['category'] ) );
        
        $this->em->persist($post);
        $this->em->flush();
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
   }
}
