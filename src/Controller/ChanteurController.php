<?php

namespace App\Controller;

use App\Entity\Chanteur;
use App\Repository\ChanteurRepository;
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

class ChanteurController extends AbstractController
{
    #[Route('/api/chanteur', name: 'chanteur', methods:['GET'])]
    public function getAllChanteur(ChanteurRepository $chanteurRepository, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cache): JsonResponse
    {
       //il est possible de croiser offset à la place de page.
       $page = $request->get('page', 1);
       $limit = $request->get('limit', 3);
       $idCache = "getAllChanteur-" . $page . "-" . $limit;

      $jsonChanteurList = $cache->get($idCache, function(ItemInterface $item) use ($chanteurRepository, $page, $limit, $serializer){
           $item->tag("chanteurCache");
           $chanteurList = $chanteurRepository->findAllWithPagination($page, $limit);
           $context = SerializationContext::create()->setGroups(['getDisques']);
           return $serializer->serialize($chanteurList, 'json', $context);
      });
      return new JsonResponse($jsonChanteurList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/chanteur/{id}', name: 'detailChanteur', methods:['GET'])]
    public function getDetailChanteur(Chanteur $chanteur, SerializerInterface $serializer): JsonResponse
    {
        $context = SerializationContext::create()->setGroups(['getDisques']);
        $jsonChanteur = $serializer->serialize($chanteur, 'json', $context);
         return new JsonResponse($jsonChanteur, Response::HTTP_OK, [], true);
       
    }

    #[Route('/api/chanteur/{id}', name: 'deleteChanteur', methods:['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour supprimer un chanteur')]
    public function deleteChanteur(Chanteur $chanteur, EntityManagerInterface $em, TagAwareCacheInterface $cache): JsonResponse
    {
        //on peut également utiliser $item->expiresAfter(60) pour préciser la durer du cache.
        $cache->invalidateTags(["chanteurCache"]);
        $em->remove($chanteur);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/chanteur', name: 'createChanteur', methods:['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour créer un chanteur')]
    public function createChanteur(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, ValidatorInterface $validator): JsonResponse
    {
        $chanteur = $serializer->deserialize($request->getContent(), Chanteur::class, 'json');
        //on vérif les erreurs.
        $errors = $validator->validate($chanteur);

        if ($errors->count() > 0) 
        {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }
        $em->persist($chanteur);
        $em->flush();

        $context = SerializationContext::create()->setGroups(['getDisques']);
        $jsonChanteur = $serializer->serialize($chanteur, 'json', $context);
        $location = $urlGenerator->generate('detailChanteur', ['id' => $chanteur->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonChanteur, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/api/chanteur/{id}', name: 'updateChanteur', methods:['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour éditer un chanteur')]
    public function updateChanteur(Request $request, SerializerInterface $serializer, Chanteur $currentChanteur, EntityManagerInterface $em, ValidatorInterface $validator, TagAwareCacheInterface $cache): JsonResponse 
    {
        $updateChanteur = $serializer->deserialize($request->getContent(), Chanteur::class, 'json');
        $currentChanteur->setNomChanteur($updateChanteur->getNomChanteur());
        //On vérif les erreurs
        $errors = $validator->validate($currentChanteur);
        if ($errors->count() > 0) {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }
        $em->persist($updateChanteur);
        $em->flush();

        //on vide le cache.
        $cache->invalidateTags(["chanteurCache"]);

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
