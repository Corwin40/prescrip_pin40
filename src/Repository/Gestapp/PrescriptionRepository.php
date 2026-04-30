<?php

namespace App\Repository\Gestapp;

use App\Entity\Gestapp\Prescription;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Prescription>
 */
class PrescriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Prescription::class);
    }

    public function filteredByStep($step)
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.step = :step')
            ->setParameter('step', $step);

        return $qb->getQuery()->getResult();
    }

    public function filteredByMultiSteps(array $excludedSteps): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.step IN (:steps)')
            ->setParameter('steps', $excludedSteps)
            ->getQuery()
            ->getResult();
    }

    public function filteredByWithoutStep($step)
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.step != :step')
            ->setParameter('step', $step);

        return $qb->getQuery()->getResult();
    }

    public function filteredByWithoutStepForMediator($step, $lieuMediation)
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.step != :step')
            ->setParameter('step', $step)
            ->andWhere('p.lieuMediation = :lieuMediation')
            ->setParameter('lieuMediation', $lieuMediation)
        ;

        return $qb->getQuery()->getResult();
    }

    public function filteredByStepForMediator($step, $lieuMediation)
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.step = :step')
            ->setParameter('step', $step)
            ->andWhere('p.lieuMediation = :lieuMediation')
            ->setParameter('lieuMediation', $lieuMediation)
        ;

        return $qb->getQuery()->getResult();
    }

    public function filteredByWithoutStepForPrescriptor($step, $prescriptor)
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.step != :step')
            ->setParameter('step', $step)
            ->andWhere('p.prescriptor = :prescriptor')
            ->setParameter('prescriptor', $prescriptor)
        ;

        return $qb->getQuery()->getResult();
    }

    public function filteredByStepForPrescriptor($step, $prescriptor)
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.step = :step')
            ->setParameter('step', $step)
            ->andWhere('p.prescriptor = :prescriptor')
            ->setParameter('prescriptor', $prescriptor)
        ;

        return $qb->getQuery()->getResult();
    }

    public function filteredByWithoutMultiSteps(array $excludedSteps): array
    {
        return $this->createQueryBuilder('u')
            ->where('u.step NOT IN (:steps)')
            ->setParameter('steps', $excludedSteps)
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Prescription[] Returns an array of Prescription objects
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

    //    public function findOneBySomeField($value): ?Prescription
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
