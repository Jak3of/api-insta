<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;



use App\Entity\User;
use App\Entity\Image;
use Symfony\Component\Serializer\SerializerInterface;

class UserController extends AbstractController
{

    private $entityManager;
    private $serializer;

    public function __construct(EntityManagerInterface $entityManager,SerializerInterface $serializer)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    
    public function index(Request $request): JsonResponse
    {


        $page = $request->get('page'); // Obtén el número de página de la consulta, por defecto es 1
        $pageSize = 7; // Define cuántos usuarios quieres mostrar por página

        

        $query = $this->entityManager->getRepository(User::class)->createQueryBuilder('u')
            ->getQuery();

        

        $paginator = new Paginator($query);
        $paginator->getQuery()
            ->setFirstResult(((int)$page - 1) * $pageSize) // Define el inicio de los resultados
            ->setMaxResults($pageSize); // Define el tamaño máximo de los resultados

        
        $users = [];
        foreach ($paginator as $user) {
            $users[] = $user;
        }

        
        # $users = $this->entityManager->getRepository(User::class)->findAll();
        
        $json = $this->serializer->serialize($users, 'json', ['groups' => ['user:read']]);
        
        $response = [
            'page' => $page,
            'users' => json_decode($json)
        ];
        
        $response['finalPage'] = ($page == ceil($paginator->count() / $pageSize));
        

        
        $response['firstPage'] = ($page == 1);
        

        return new JsonResponse($response, Response::HTTP_OK);
    }

    public function show(int $id): JsonResponse
    {
        
        

        $user = $this->entityManager->getRepository(User::class)->find($id);

        if ($user) {
            $json = $this->serializer->serialize($user, 'json', ['groups' => ['user:read']]);
            return new JsonResponse($json, Response::HTTP_OK, [], true);
        } else {
            return new JsonResponse( 'User not found', Response::HTTP_NOT_FOUND);
        }
    }

    public function userPosts(int $id): JsonResponse
    {


        $images = $this->entityManager->getRepository(Image::class)->findBy(['user' => $id]);
        $json = $this->serializer->serialize($images, 'json', ['groups' => ['image:read']]);
        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    public function userNick(Request $request): JsonResponse
    {

        $nick = $request->get('nick');
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['nick' => $nick]);
        $json = $this->serializer->serialize($user, 'json', ['groups' => ['viewuser:read']]);
        return new JsonResponse($json, Response::HTTP_OK, [], true);
    }

    public function userExists(Request $request): JsonResponse
    {

        $nick = $request->get('nick');
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['nick' => $nick]);
        if ($user) {
            return new JsonResponse(['exists' => true], Response::HTTP_OK);
        } else {
            return new JsonResponse(['exists' => false], Response::HTTP_OK);
        }
    }

    

}
