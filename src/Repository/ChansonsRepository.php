<?php

namespace App\Repository;

use App\Entity\Chansons;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Chansons>
 */
class ChansonsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Chansons::class);
    }

    public function findAllWithPagination($page, $limit) 
    {
        $qb = $this->createQueryBuilder('b')
            ->setFirstResult(($page -1)* $limit)
            ->setMaxResults($limit);

        $query = $qb->getQuery();
        $query->setFetchMode(Chansons::class, "disque", \Doctrine\ORM\Mapping\ClassMetadata::FETCH_EAGER);
            return $query->getResult();
    }

    //    /**
    //     * @return Chansons[] Returns an array of Chansons objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Chansons
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
