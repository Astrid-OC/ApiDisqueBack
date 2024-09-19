<?php

namespace App\Controller;

use App\Entity\Disque;
use App\Repository\DisqueRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DisqueController extends AbstractController
{
    #[Route('/api/disque', name: 'disque', methods:['GET'])]
    public function getAllDisque(DisqueRepository $disqueRepository, SerializerInterface $serializer): JsonResponse
    {
       $disqueList = $disqueRepository->findAll();
       $jsonDisqueList = $serializer->serialize($disqueList, 'json');
       return new JsonResponse($jsonDisqueList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/disque/{id}', name: 'detailDisque', methods:['GET'])]
    public function getDetailDisque(Disque $disque, SerializerInterface $serializer): JsonResponse
    {
        $jsonDisque = $serializer->serialize($disque, 'json');
         return new JsonResponse($jsonDisque, Response::HTTP_OK, ['accept' => 'json'], true);
       
    }
}
