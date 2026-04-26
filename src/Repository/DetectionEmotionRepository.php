<?php

namespace App\Repository;

use App\Entity\DetectionEmotion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DetectionEmotionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DetectionEmotion::class);
    }

    public function findByHumeur(int $humeurId): ?DetectionEmotion
    {
        return $this->findOneBy(['humeurJournaliere' => $humeurId]);
    }
}