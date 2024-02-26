<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class HomeController extends AbstractController
{

    private $entityManager;
    private $serializer;
    private $validator;
    private $passwordEncoder;

    public function __construct(
        EntityManagerInterface $entityManager, 
        SerializerInterface $serializer, 
        ValidatorInterface $validator, 
        UserPasswordHasherInterface $passwordEncoder
        )
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->passwordEncoder = $passwordEncoder;
    }
    
    
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/HomeController.php',
        ]);
    }

    public function register(Request $request) : JsonResponse
    {
        $user = new User();
        
        $data = json_decode($request->getContent(), true);
        
        $user->setName($data['name']);
        $user->setEmail($data['email']);
        $user->setPassword($data['password']);
        $user->setNick($data['nick']);
        $user->setSurname($data['surname']);
        $user->setRole('user');
        $user->setImage('default.png');
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setUpdatedAt(new \DateTimeImmutable());

        $errors = $this->validator->validate($user);

        if (count($errors) > 0) {
            $errors = $this->serializer->serialize($errors, 'json');
            return new JsonResponse($errors, Response::HTTP_BAD_REQUEST, [], true);
        } else {
            $entityManager = $this->entityManager->getRepository(User::class);


             // Inicializa como arrays
            $response = [
                'code' => Response::HTTP_UNPROCESSABLE_ENTITY
            ];
            if ($entityManager->findOneBy(['email' => $user->getEmail()])) {
                $response['data'][] = 'email';
                $response['messageemail'] ='Email already exists';
            }
            if ($entityManager->findOneBy(['nick' => $user->getNick()])) {
                $response['data'][] = 'nick';
                $response['messagenick'] ='Nick already exists';
            }
            if (!empty($response['data'])) {

                return new JsonResponse(
                    $response, 
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }
 
            else {
                $user->setPassword(
                    $this->passwordEncoder->hashPassword($user, $user->getPassword())
                );
                
                $this->entityManager->persist($user);
                $this->entityManager->flush();
                return new JsonResponse( 
                    [
                        'id' => $user->getId(),
                        'message' => 'User created successfully'
                    ],
                     Response::HTTP_OK);
            }
        }
    }

    public function getImage(string $filename): Response
    {
        $filesystem = new Filesystem();
        $path = __DIR__ . '/../../public/uploads/images/users/' . $filename;
        if ($filesystem->exists($path)) {
            $fileContent = file_get_contents($path);
            $mimeType = mime_content_type($path);
            return new Response($fileContent, 200, ['Content-Type' => $mimeType]);
        }
        return new JsonResponse(['message' => 'Image not found'], Response::HTTP_NOT_FOUND);
    }

    
}
