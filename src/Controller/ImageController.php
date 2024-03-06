<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Entity\Image;
use App\Entity\User;
use App\Entity\Like;
use App\Entity\Comment;

class ImageController extends AbstractController
{

    private $entityManager;
    private $serializer;
    private $validator;
    private $tokenStorage;
    private $jwtManager;

    public function __construct(
        EntityManagerInterface $entityManager, 
        SerializerInterface $serializer, 
        ValidatorInterface $validator, 
        TokenStorageInterface $tokenStorage,
        JWTTokenManagerInterface $jwtManager
        )
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->tokenStorage = $tokenStorage;
        $this->jwtManager = $jwtManager;
    }
    
    public function index(Request $request): JsonResponse
    {
        $page = $request->get('page'); // Obtén el número de página de la consulta, por defecto es 1
        $pageSize = 10; // Define cuántos usuarios quieres mostrar por página

        $query = $this->entityManager->getRepository(Image::class)->createQueryBuilder('u')
            ->orderBy('u.created_at', 'DESC')
            ->getQuery();
        
        $paginator = new Paginator($query);
        $paginator->getQuery()
            ->setFirstResult(((int)$page - 1) * $pageSize) // Define el inicio de los resultados
            ->setMaxResults($pageSize); // Define el tamaño máximo de los resultados
        
        // usuario logueado mediante token jwt

        // "user": {
        //     "iat": 1709239850,
        //     "exp": 1709326250,
        //     "roles": [
        //         "ROLE_ADMIN"
        //     ],
        //     "email": "paco@gmail.com"
        // }


        

        $images = [];
        foreach ($paginator as $image) {
            $image->setlikedByCurrentUser(
                $this->tokenStorage->getToken()->getUser()->getEmail()
            );
            $images[] = $image;
        }

        $json = $this->serializer->serialize($images, 'json', ['groups' => ['image:read']]);

        return new JsonResponse( $json, 200, [], true);
    }


    public function getPosts(string $filename): Response
    {
        $filesystem = new Filesystem();
        $path = __DIR__ . '/../../public/uploads/images/posts/' . $filename;
        if ($filesystem->exists($path)) {
            $fileContent = file_get_contents($path);
            $mimeType = mime_content_type($path);
            return new Response($fileContent, 200, ['Content-Type' => $mimeType]);
        }
        return new JsonResponse(['message' => 'Image not found'], Response::HTTP_NOT_FOUND);
    }
    

    public function show(Request $request) : JsonResponse
    {
        $id = $request->get('id');
        $image = $this->entityManager->getRepository(Image::class)->find($id);


        
        if (!$image) {
            return new JsonResponse(['message' => 'Image not found'], 404);
        }

        $image->setlikedByCurrentUser(
            $this->tokenStorage->getToken()->getUser()->getEmail()
        );
        $json = $this->serializer->serialize($image, 'json', ['groups' => ['post:read']]);
        
        return new JsonResponse($json, 200, [], true);
    }

    public function like(Request $request) : JsonResponse
    {
        $id = $request->get('id');
        $image = $this->entityManager->getRepository(Image::class)->find($id);
        if (!$image) {
            return new JsonResponse(['message' => 'Image not found'], 404);
        }

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $this->tokenStorage->getToken()->getUser()->getEmail()]);

        if (!$user) {
            return new JsonResponse(['message' => 'User not found'], 404);
        }

        $like = new Like();
        $like->setCreatedAt(new \DateTimeImmutable());
        $like->setUpdatedAt(new \DateTimeImmutable());
        $like->setImage($image);
        $like->setUser($user);
        $this->entityManager->persist($like);
        
        $this->entityManager->flush();
        return new JsonResponse(['message' => 'Image liked'], 200);
    }

    public function dislike(Request $request) : JsonResponse
    {
        $id = $request->get('id');
        $image = $this->entityManager->getRepository(Image::class)->find($id);
        if (!$image) {
            return new JsonResponse(['message' => 'Image not found'], 404);
        }

        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $this->tokenStorage->getToken()->getUser()->getEmail()]);

        if (!$user) {
            return new JsonResponse(['message' => 'User not found'], 404);
        }

        $like = $this->entityManager->getRepository(Like::class)->findOneBy([
            'image' => $image,
            'user' => $user
        ]);
        if (!$like) {
            return new JsonResponse(['message' => 'Like not found'], 404);
        }

        $this->entityManager->remove($like);
        $this->entityManager->flush();
        return new JsonResponse(['message' => 'Image disliked'], 200);
        

    }

    public function comment(Request $request) : JsonResponse
    {

        $content = $request->getContent();
        $content = json_decode($content, true);

        $comment = new Comment();
        $comment->setCreatedAt(new \DateTimeImmutable());
        $comment->setUpdatedAt(new \DateTimeImmutable());
        $comment->setContent( $content['comment'] );
        $comment->setUser($this->tokenStorage->getToken()->getUser());
        $comment->setImage($this->entityManager->getRepository(Image::class)->find($request->get('id')));
        $this->entityManager->persist($comment);
        $this->entityManager->flush();

        $json = $this->serializer->serialize($comment, 'json', ['groups' => ['comment:read']]);

        return new JsonResponse($json, 200, [], true);

    }

    public function upload(Request $request) : JsonResponse
    {

        $file = $request->files->get('file');


        if (!$request->get('description')) {
            return new JsonResponse(['message' => 'Description not found'], 404);
        }
        if (!$file) {
            return new JsonResponse(['message' => 'File not found'], 404);
        }

        $filename = time() . $file->getClientOriginalName();

        

        // prueba
        

        

        $image = new Image();
        $image->setCreatedAt(new \DateTimeImmutable());
        $image->setUpdatedAt(new \DateTimeImmutable());
        $image->setUser($this->tokenStorage->getToken()->getUser());
        $image->setImagePath($filename);
        $image->setDescription($request->get('description'));

        $filesystem = new Filesystem();
        $filesystem->copy($file, __DIR__ . '/../../public/uploads/images/posts/' . $filename);

        $this->entityManager->persist($image);
        $this->entityManager->flush();

        
    
        $json = $this->serializer->serialize($image, 'json', ['groups' => ['image:read']]);
        return new JsonResponse($json, 200, [], true);
    }





}
