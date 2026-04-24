<?php

namespace App\Repository;

use App\Entity\Objectif;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Objectif>
 */
class ObjectifRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Objectif::class);
    }

    /**
     * Récupère tous les objectifs en cours
     */
    public function findObjectifsEnCours(): array
    {
        return $this->createQueryBuilder('o')
            ->where('o.statut = :statut')
            ->setParameter('statut', Objectif::STATUT_EN_COURS)
            ->orderBy('o.date_fin', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les objectifs qui se terminent aujourd'hui
     */
    public function findObjectifsTerminesAujourdhui(): array
    {
        $today = new \DateTime();
        $today->setTime(0, 0, 0);
        $tomorrow = clone $today;
        $tomorrow->modify('+1 day');

        return $this->createQueryBuilder('o')
            ->where('o.date_fin >= :today')
            ->andWhere('o.date_fin < :tomorrow')
            ->andWhere('o.statut = :statut')
            ->setParameter('today', $today)
            ->setParameter('tomorrow', $tomorrow)
            ->setParameter('statut', Objectif::STATUT_EN_COURS)
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les objectifs atteints
     */
    public function findObjectifsAtteints(): array
    {
        return $this->createQueryBuilder('o')
            ->where('o.statut = :statut')
            ->setParameter('statut', Objectif::STATUT_ATTEINT)
            ->orderBy('o.date_fin', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les objectifs non atteints
     */
    public function findObjectifsNonAtteints(): array
    {
        return $this->createQueryBuilder('o')
            ->where('o.statut = :statut')
            ->setParameter('statut', Objectif::STATUT_NON_ATTEINT)
            ->orderBy('o.date_fin', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les objectifs par période
     */
    public function findObjectifsEntreDates(\DateTime $debut, \DateTime $fin): array
    {
        return $this->createQueryBuilder('o')
            ->where('o.date_debut >= :debut')
            ->andWhere('o.date_fin <= :fin')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->orderBy('o.date_debut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte les objectifs par statut
     */
    public function countByStatut(): array
    {
        $qb = $this->createQueryBuilder('o');
        
        return [
            'total' => $qb->select('COUNT(o.id)')->getQuery()->getSingleScalarResult(),
            'en_cours' => (clone $qb)->where('o.statut = :statut')->setParameter('statut', Objectif::STATUT_EN_COURS)->getQuery()->getSingleScalarResult(),
            'atteint' => (clone $qb)->where('o.statut = :statut')->setParameter('statut', Objectif::STATUT_ATTEINT)->getQuery()->getSingleScalarResult(),
            'non_atteint' => (clone $qb)->where('o.statut = :statut')->setParameter('statut', Objectif::STATUT_NON_ATTEINT)->getQuery()->getSingleScalarResult(),
        ];
    }
}