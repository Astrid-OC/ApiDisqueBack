<?php

namespace App\Controller;

use App\Entity\Chanteur;
use App\Repository\ChanteurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ChanteurController extends AbstractController
{
    #[Route('/api/chanteur', name: 'chanteur', methods:['GET'])]
    public function getAllChanteur(ChanteurRepository $chanteurRepository, SerializerInterface $serializer): JsonResponse
    {
       $chanteurList = $chanteurRepository->findAll();
       $jsonChanteurList = $serializer->serialize($chanteurList, 'json', ['groups' => 'getDisques']);
       return new JsonResponse($jsonChanteurList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/chanteur/{id}', name: 'detailChanteur', methods:['GET'])]
    public function getDetailChanteur(Chanteur $chanteur, SerializerInterface $serializer): JsonResponse
    {
        $jsonChanteur = $serializer->serialize($chanteur, 'json', ['groups' => 'getDisques']);
         return new JsonResponse($jsonChanteur, Response::HTTP_OK, [], true);
       
    }

    #[Route('/api/chanteur/{id}', name: 'deleteChanteur', methods:['DELETE'])]
    public function deleteChanteur(Chanteur $chanteur, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($chanteur);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/chanteur', name: 'createChanteur', methods:['POST'])]
    public function createChanteur(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator): JsonResponse
    {
        $chanteur = $serializer->deserialize($request->getContent(), Chanteur::class, 'json');
        $em->persist($chanteur);
        $em->flush();

        $jsonChanteur = $serializer->serialize($chanteur, 'json', ['groups' => 'getDisques']);
        $location = $urlGenerator->generate('detailChanteur', ['id' => $chanteur->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonChanteur, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/api/chanteur/{id}', name: 'updateChanteur', methods:['PUT'])]
    public function updateChanteur(Request $request, SerializerInterface $serializer, Chanteur $currentChanteur, EntityManagerInterface $em): JsonResponse 
    {
        $updateChanteur = $serializer->deserialize($request->getContent(), Chanteur::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $currentChanteur]);
        $em->persist($updateChanteur);
        $em->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
