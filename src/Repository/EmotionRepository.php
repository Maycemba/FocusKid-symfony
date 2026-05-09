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

    // Define your custom method here
    public function findWithFiltersQuery($filters = null, string $orderBy = 'id', string $orderDir = 'ASC'): Query
    {
        $qb = $this->createQueryBuilder('e');
        
        // Add your filter logic here
        // Example:
        if ($filters && isset($filters['name'])) {
            $qb->andWhere('e.name LIKE :name')
               ->setParameter('name', '%' . $filters['name'] . '%');
        }
        
        $qb->orderBy('e.' . $orderBy, $orderDir);
        
        return $qb->getQuery();
    }
//    /**
//     * @return Emotion[] Returns an array of Emotion objects
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

//    public function findOneBySomeField($value): ?Emotion
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
