<?php

namespace App\Controller;

use App\Entity\Disque;
use App\Repository\ChanteurRepository;
use App\Repository\DisqueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

class DisqueController extends AbstractController
{
    #[Route('/api/disque', name: 'disque', methods:['GET'])]
    public function getAllDisque(DisqueRepository $disqueRepository, SerializerInterface $serializer): JsonResponse
    {
       $disqueList = $disqueRepository->findAll();
       $jsonDisqueList = $serializer->serialize($disqueList, 'json', ['groups' => 'getDisques']);
       return new JsonResponse($jsonDisqueList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/disque/{id}', name: 'detailDisque', methods:['GET'])]
    public function getDetailDisque(Disque $disque, SerializerInterface $serializer): JsonResponse
    {
        $jsonDisque = $serializer->serialize($disque, 'json', ['groups' => 'getDisques']);
         return new JsonResponse($jsonDisque, Response::HTTP_OK, [], true);
       
    }

    #[Route('/api/disque/{id}', name: 'deleteDisque', methods:['DELETE'])]
    public function deleteDisque(Disque $disque, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($disque);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/disque', name: 'createDisque', methods:['POST'])]
    public function createDisque(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, ChanteurRepository $chanteurRepository): JsonResponse
    {
        $disque = $serializer->deserialize($request->getContent(), Disque::class, 'json');
        //Récup de l'ensemble des données envoyées sous forme de tableau.
        $content = $request->toArray();
        //Récup de l'idChanteur. S'il n'est pas défini, alors on met -1 par défaut.
        $idChanteur = $content['idChanteur'] ?? -1;
        //On cherche l'auteur qui correspond et on l'assigne au disque, si find ne trouve pas le chanteur alros null sera retoourné.
        $disque->setChanteur($chanteurRepository->find($idChanteur));
        $em->persist($disque);
        $em->flush();

        $jsonDisque = $serializer->serialize($disque, 'json', ['groups' => 'getDisques']);
        $location = $urlGenerator->generate('detailDisque', ['id' => $disque->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonDisque, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/api/disque/{id}', name: 'updateDisque', methods:['PUT'])]
    public function updateDisque(Request $request, SerializerInterface $serializer, Disque $currentDisque, EntityManagerInterface $em, ChanteurRepository $chanteurRepository): JsonResponse 
    {
        $updateDisque = $serializer->deserialize($request->getContent(), Disque::class, 'json', [AbstractNormalizer::OBJECT_TO_POPULATE => $currentDisque]);
        $content = $request->toArray();
        $idChanteur = $content['idChanteur'] ?? -1;

        $updateDisque->setChanteur($chanteurRepository->find($idChanteur));

        $em->persist($updateDisque);
        $em->flush();

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
