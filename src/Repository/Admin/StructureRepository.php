<?php

namespace App\Repository\Admin;

use App\Entity\Admin\Structure;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Structure>
 */
class StructureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Structure::class);
    }

    public function findPrescriptorsByPrescriptor($structure): array
    {
        return $this->createQueryBuilder('s')
            ->where('s = :idstructure')
            ->setParameter('idstructure', $structure)
            //->where('s.roles LIKE :role')
            //->setParameter('role', '%ROLE_PRESCRIPTEUR%')
            ->getQuery()
            ->getResult();
    }

    public function findPrescriptorsByMediator($structure): array
    {
        return $this->createQueryBuilder('s')
            ->select('DISTINCT s')
            ->join('s.members', 'm')
            ->join('m.referent', 'r')
            ->join('r.structure', 'st')
            ->where('st = :idstructure')
            ->setParameter('idstructure', $structure)
            //->where('s.roles LIKE :role')
            //->setParameter('role', '%ROLE_PRESCRIPTEUR%')
            ->getQuery()
            ->getResult();
    }

    public function findPrescriptorsByAdmin(): array
    {
        return $this->createQueryBuilder('s')
            ->join('s.members', 'm')
            ->join('m.referent', 'r')
            //->andWhere('r.roles LIKE :admin OR r.roles LIKE :superAdmin')
            //->setParameter('admin', '%ROLE_ADMIN%')
            //->setParameter('superAdmin', '%ROLE_SUPER_ADMIN%')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Structure[] Returns an array of Structure objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Structure
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
