<?php

namespace App\Controller;

use App\Entity\Chansons;
use App\Repository\ChansonsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ChansonsController extends AbstractController
{
    #[Route('/api/chansons', name: 'chansons', methods:['GET'])]
    public function getAllChansons(ChansonsRepository $chansonsRepository, SerializerInterface $serializer): JsonResponse
    {
       $chansonsList = $chansonsRepository->findAll();
       $jsonChansonsList = $serializer->serialize($chansonsList, 'json', ['groups' => 'getDisques']);
       return new JsonResponse($jsonChansonsList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/chansons/{id}', name: 'detailChansons', methods:['GET'])]
    public function getDetailChansons(Chansons $chansons, SerializerInterface $serializer): JsonResponse
    {
        $jsonChansons = $serializer->serialize($chansons, 'json', ['groups' => 'getDisques']);
         return new JsonResponse($jsonChansons, Response::HTTP_OK, [], true);
       
    }

    #[Route('/api/chansons/{id}', name: 'deleteChansons', methods:['DELETE'])]
    public function deleteChansons(Chansons $chansons, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($chansons);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/chansons', name: 'createChansons', methods:['POST'])]
    public function createChansons(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $chansons = $serializer->deserialize($request->getContent(), Chansons::class, 'json');
        $em->persist($chansons);
        $em->flush();

        $jsonChansons = $serializer->serialize($chansons, 'json', ['groups' => 'getDisques']);
        $location = $urlGenerator->generate('detailChansons', ['id' => $chansons->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonChansons, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/api/chansons/{id}', name: 'updateChansons', methods:['PUT'])]
    public function updateChansons(Request $request, SerializerInterface $serializer, Chansons $currentChansons, EntityManagerInterface $em): JsonResponse 
    {
        $updateChansons = $serializer->deserialize($request->getContent(), Chansons::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $currentChansons]);
        $em->persist($updateChansons);
        $em->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
