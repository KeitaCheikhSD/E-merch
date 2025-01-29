<?php

namespace App\Repository;

use App\Entity\EquipeEsport;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EquipeEsport>
 */
class EquipeEsportRepository extends ServiceEntityRepository
{
    private $connexion ;
    
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EquipeEsport::class);
    }


    //    /**
    //     * @return EquipeEsport[] Returns an array of EquipeEsport objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?EquipeEsport
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
