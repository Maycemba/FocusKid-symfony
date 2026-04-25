<?php

namespace App\Repository;

use App\Entity\Scenario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
     * Recherche et filtre des scénarios par description et émotion
     */
    public function findWithFilters(?string $search = null, ?int $emotionId = null): array
    {
        $qb = $this->createQueryBuilder('s')
                   ->leftJoin('s.emotion', 'e')
                   ->addSelect('e');

        if ($search) {
            $qb->andWhere('s.description LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        if ($emotionId) {
            $qb->andWhere('e.id = :emotionId')
               ->setParameter('emotionId', $emotionId);
        }

        $qb->orderBy('s.id', 'ASC');

        return $qb->getQuery()->getResult();
    }
}