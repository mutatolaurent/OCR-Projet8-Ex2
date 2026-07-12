<?php

namespace App\Repository;

use App\Entity\Projet;
use App\Entity\Employe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Projet>
 */
class ProjetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Projet::class);
    }

    //    /**
    //     * @return Projet[] Returns an array of Projet objects
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

    //    public function findOneBySomeField($value): ?Projet
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    // Récupère les projets non archivés pour un employé donné
    public function findNonArchivedProjectsForEmploye(Employe $employe): array
    {
        return $this->createQueryBuilder('p')
            ->join('p.employes', 'e')
            ->andWhere('e.id = :employeId')
            ->andWhere('p.archive = :archive')
            ->setParameter('employeId', $employe->getId())
            ->setParameter('archive', false)
            ->orderBy('p.id', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
