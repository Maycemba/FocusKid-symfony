<?php

namespace App\Repository;

use App\Entity\Emotion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Query;

/**
 * @extends ServiceEntityRepository<Emotion>
 */
class EmotionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Emotion::class);
    }

    /**
     * Recherche et tri des émotions
     */
    public function findWithFilters(?string $search = null, string $sort = 'id', string $order = 'ASC'): array
    {
        $qb = $this->createQueryBuilder('e');

        if ($search) {
            $qb->andWhere('e.nom LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        $allowedSorts = ['id', 'nom'];
        $allowedOrders = ['ASC', 'DESC'];

        $sortField = in_array($sort, $allowedSorts) ? $sort : 'id';
        $orderDir  = in_array(strtoupper($order), $allowedOrders) ? strtoupper($order) : 'ASC';

        $qb->orderBy('e.' . $sortField, $orderDir);

        return $qb->getQuery()->getResult();
    }
public function findWithFiltersQuery(?string $search, string $sort, string $order): Query
{
    $qb = $this->createQueryBuilder('e');
    if ($search) {
        $qb->where('e.nom LIKE :search')->setParameter('search', '%'.$search.'%');
    }
    return $qb->orderBy('e.'.$sort, $order)->getQuery();
}
}