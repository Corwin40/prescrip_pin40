<?php

namespace App\Repository\Gestapp;

use App\Entity\Gestapp\Beneficiary;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Beneficiary>
 */
class BeneficiaryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Beneficiary::class);
    }

    public function findByMediation($structure): array
    {
        return $this->createQueryBuilder('b')
            //->select('DISTINCT b')
            ->join('b.structure', 's')
            ->join('s.members', 'm')
            ->join('m.referent', 'r')
            ->join('r.structure', 'rs')
            ->where('rs.id = :idstructure')
            ->setParameter('idstructure', $structure->getId())
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Beneficiary[] Returns an array of Beneficiary objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('b.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Beneficiary
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
