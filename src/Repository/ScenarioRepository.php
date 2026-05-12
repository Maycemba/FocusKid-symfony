<?php

namespace App\Repository;

use App\Entity\Scenario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Scenario>
 */
class ScenarioRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Scenario::class);
    }

    /**
     * Creates a QueryBuilder for scenarios with optional filters.
     *
     * @param string|null $search    Filter by description (partial match)
     * @param int|null    $emotionId Filter by emotion ID
     *
     * @return QueryBuilder
     */
    public function findWithFiltersQuery(?string $search, ?int $emotionId): QueryBuilder
    {
        $qb = $this->createQueryBuilder('s')
            ->leftJoin('s.emotion', 'e')
            ->addSelect('e'); // eager load emotion to avoid extra queries

        // Apply description search filter
        if (!empty($search)) {
            $qb->andWhere('s.description LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        // Apply emotion filter
        if ($emotionId !== null) {
            $qb->andWhere('e.id = :emotionId')
               ->setParameter('emotionId', $emotionId);
        }

        // Order by ID descending (or any other default sorting)
        $qb->orderBy('s.id', 'DESC');

        return $qb;
    }
}