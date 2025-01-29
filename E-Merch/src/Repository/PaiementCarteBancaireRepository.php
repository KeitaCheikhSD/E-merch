<?php

namespace App\Repository;

use App\Entity\PaiementCarteBancaire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PaiementCarteBancaire>
 */
class PaiementCarteBancaireRepository extends ServiceEntityRepository
{
    private $connexion ; 

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaiementCarteBancaire::class);
    }

    //    /**
    //     * @return PaiementCarteBancaire[] Returns an array of PaiementCarteBancaire objects
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

    //    public function findOneBySomeField($value): ?PaiementCarteBancaire
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
