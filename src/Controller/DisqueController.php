<?php

namespace App\Controller;

use App\Entity\Disque;
use App\Repository\ChanteurRepository;
use App\Repository\DisqueRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use App\Services\VersioningService;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use OpenApi\Annotations as OA;

class DisqueController extends AbstractController
{
    /**
    * Cette méthode permet de récupérer l'ensemble des livres.
    *
    * @OA\Response(
    * response=200,
    * description="Retourne la liste des disques",
    * @OA\JsonContent(
    * type="array",
    * @OA\Items(ref=@Model(type= Disques::class, groups={"getDisques"}))
    * )
    * )
    * @OA\Parameter(
    * name="page",
    * in="query",
    * description="La page que l'on veut récupérer",
    * @OA\Schema(type="int")
    * )
    *
    * @OA\Parameter(
    * name="limit",
    * in="query",
    * description="Le nombre d'éléments que l'on veut récupérer",
    * @OA\Schema(type="int")
    * )
    * @OA\Tag(name="Disques")
    *
    * @param DisqueRepository $bookRepository
    * @param SerializerInterface $serializer
    * @param Request $request
    * @return JsonResponse
    */
    #[Route('/api/disque', name: 'disque', methods:['GET'])]
    public function getAllDisque(DisqueRepository $disqueRepository, SerializerInterface $serializer, Request $request, TagAwareCacheInterface $cache): JsonResponse
    {
        //il est possible de croiser offset à la place de page.
        $page = $request->get('page', 1);
        $limit = $request->get('limit', 3);
        $idCache = "getAllDisque-" . $page . "-" . $limit;

       $jsonDisqueList = $cache->get($idCache, function(ItemInterface $item) use ($disqueRepository, $page, $limit, $serializer){
            $item->tag("disqueCache");
            $disqueList = $disqueRepository->findAllWithPagination($page, $limit);
            $context = SerializationContext::create()->setGroups(['getDisques']);
            return $serializer->serialize($disqueList, 'json', $context);
       });
       return new JsonResponse($jsonDisqueList, Response::HTTP_OK, [], true);
    }

    #[Route('/api/disque/{id}', name: 'detailDisque', methods:['GET'])]
    public function getDetailDisque(Disque $disque, SerializerInterface $serializer, VersioningService $versioningService): JsonResponse
    {
        $version = $versioningService->getVersion();
        $context = SerializationContext::create()->setGroups(['getDisques']);
        $context->setVersion($version);
        $jsonDisque = $serializer->serialize($disque, 'json', $context);
         return new JsonResponse($jsonDisque, Response::HTTP_OK, [], true);
       
    }

    #[Route('/api/disque/{id}', name: 'deleteDisque', methods:['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour supprimer un disque')]
    public function deleteDisque(Disque $disque, EntityManagerInterface $em, TagAwareCacheInterface $cache): JsonResponse
    {
        //on peut également utiliser $item->expiresAfter(60) pour préciser la durer du cache.
        $cache->invalidateTags(["disqueCache"]);
        $em->remove($disque);
        $em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/disque', name: 'createDisque', methods:['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour créer un disque')]
    public function createDisque(Request $request, SerializerInterface $serializer, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, ChanteurRepository $chanteurRepository, ValidatorInterface $validator): JsonResponse
    {
        $disque = $serializer->deserialize($request->getContent(), Disque::class, 'json');
        //on vérif les erreurs.
        $errors = $validator->validate($disque);

        if ($errors->count() > 0) 
        {
            return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $em->persist($disque);
        $em->flush();
        
        //Récup de l'ensemble des données envoyées sous forme de tableau.
        $content = $request->toArray();
        //Récup de l'idChanteur. S'il n'est pas défini, alors on met -1 par défaut.
        $idChanteur = $content['idChanteur'] ?? -1;
        //On cherche l'auteur qui correspond et on l'assigne au disque, si find ne trouve pas le chanteur alros null sera retoourné.
        $disque->setChanteur($chanteurRepository->find($idChanteur));
        $context = SerializationContext::create()->setGroups(['getDisques']);
        $jsonDisque = $serializer->serialize($disque, 'json', $context);
        $location = $urlGenerator->generate('detailDisque', ['id' => $disque->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse($jsonDisque, Response::HTTP_CREATED, ["Location" => $location], true);
    }

    #[Route('/api/disque/{id}', name: 'updateDisque', methods:['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour éditer un disque')]
    public function updateDisque(Request $request, SerializerInterface $serializer, Disque $currentDisque, EntityManagerInterface $em, ChanteurRepository $chanteurRepository, ValidatorInterface $validator, TagAwareCacheInterface $cache): JsonResponse 
    {
        $updateDisque = $serializer->deserialize($request->getContent(), Disque::class, 'json');
        $currentDisque->setNomDisque($updateDisque->getNomDisque());
        //on vérif les erreurs
        $errors = $validator->validate($currentDisque);
        if ($errors->count() > 0) 
        {
           return new JsonResponse($serializer->serialize($errors, 'json'), JsonResponse::HTTP_BAD_REQUEST, [], true);
        }

        $content = $request->toArray();
        $idChanteur = $content['idChanteur'] ?? -1;

        $currentDisque->setChanteur($chanteurRepository->find($idChanteur));

        $em->persist($currentDisque);
        $em->flush();
        //on vide le cache.
        $cache->invalidateTags(["disqueCache"]);

        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
    }
}
