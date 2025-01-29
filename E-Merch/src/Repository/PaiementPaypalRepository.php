<?php

namespace App\Repository;

use App\Entity\PaiementPaypal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PaiementPaypal>
 */
class PaiementPaypalRepository extends ServiceEntityRepository
{
    private $connexion ; 

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaiementPaypal::class);
    }

    //    /**
    //     * @return PaiementPaypal[] Returns an array of PaiementPaypal objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?PaiementPaypal
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
