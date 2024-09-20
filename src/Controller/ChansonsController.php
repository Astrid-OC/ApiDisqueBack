<?php

namespace App\Controller;

use App\Entity\Chansons;
use App\Repository\ChansonsRepository;
use JMS\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ChansonsController extends AbstractController
{
    #[Route('/api/chansons', name: 'chansons', methods:['GET'])]
    public function getAllChansons(ChansonsRepository $chansonsRepository, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cache): JsonResponse
    {
       //il est possible de croiser offset à la place de page.
       $page = $request->get('page', 1);
       $limit = $request->get('limit', 3);
       $idCache = "getAllChansons-" . $page . "-" . $limit;

      $jsonChansonsList = $cache->get($idCache, function(ItemInterface $item) use ($chansonsRepository, $page, $limit, $serializer){
           $item->tag("chansonsCache");
           $chansonsList = $chansonsRepository->findAllWithPagination($page, $limit);
           $context = SerializationContext::create()->setGroups(['getDisques']);
           return $serializer->serialize($chansonsList, 'json', $context);
      });
      return new JsonResponse($jsonChansonsList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/chansons/{id}', name: 'detailChansons', methods:['GET'])]
    public function getDetailChansons(Chansons $chansons, SerializerInterface $serializer): JsonResponse
    {
        $context = SerializationContext::create()->setGroups(['getDisques']);
        $jsonChansons = $serializer->serialize($chansons, 'json', $context);
         return new JsonResponse($jsonChansons, Response::HTTP_OK, [], true);
       
    }

    #[Route('/api/chansons/{id}', name: 'deleteChansons', methods:['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour supprimer une chanson')]
    public function deleteChansons(Chansons $chansons, EntityManagerInterface $em, TagAwareCacheInterface $cache): JsonResponse
    {
        //on peut également utiliser $item->expiresAfter(60) pour préciser la durer du cache.
        $cache->invalidateTags(["chansonsCache"]);
        $em->remove($chansons);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/chansons', name: 'createChansons', methods:['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour créer une chanson')]
    public function createChansons(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator): JsonResponse
    {
        $chansons = $serializer->deserialize($request->getContent(), Chansons::class, 'json');
        //on vérif les erreurs.
        $errors = $validator->validate($chansons);

        if ($errors->count() > 0) 
        {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }
        $em->persist($chansons);
        $em->flush();

        $context = SerializationContext::create()->setGroups(['getDisques']);
        $jsonChansons = $serializer->serialize($chansons, 'json', $context);
        $location = $urlGenerator->generate('detailChansons', ['id' => $chansons->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonChansons, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/api/chansons/{id}', name: 'updateChansons', methods:['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour éditer une chanson')]
    public function updateChansons(Request $request, SerializerInterface $serializer, Chansons $currentChansons, EntityManagerInterface $em, ValidatorInterface $validator, TagAwareCacheInterface $cache): JsonResponse 
    {
        $updateChansons = $serializer->deserialize($request->getContent(), Chansons::class, 'json');
        $currentChansons->setTitre($updateChansons->getTitre());
        $currentChansons->setDuree($updateChansons->getDuree());

        //on vérif les erreurs.
        $errors = $validator->validate($currentChansons);

        if ($errors->count() > 0) 
        {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }
        $em->persist($updateChansons);
        $em->flush();

        //on vide le cache.
        $cache->invalidateTags(["chansonsCache"]);

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
