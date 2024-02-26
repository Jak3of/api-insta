<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Entity\Image;

class ImageController extends AbstractController
{

    private $entityManager;
    private $serializer;
    private $validator;

    public function __construct(
        EntityManagerInterface $entityManager, 
        SerializerInterface $serializer, 
        ValidatorInterface $validator, 
        )
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->validator = $validator;
    }
    
    public function index(Request $request): JsonResponse
    {
        $page = $request->get('page'); // Obtén el número de página de la consulta, por defecto es 1
        $pageSize = 10; // Define cuántos usuarios quieres mostrar por página

        $query = $this->entityManager->getRepository(Image::class)->createQueryBuilder('u')
            ->getQuery();
        
        $paginator = new Paginator($query);
        $paginator->getQuery()
            ->setFirstResult(((int)$page - 1) * $pageSize) // Define el inicio de los resultados
            ->setMaxResults($pageSize); // Define el tamaño máximo de los resultados
        
        $images = [];
        foreach ($paginator as $image) {
            $images[] = $image;
        }

        $json = $this->serializer->serialize($images, 'json', ['groups' => ['image:read']]);

        return new JsonResponse($json, 200, [], true);
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
}
