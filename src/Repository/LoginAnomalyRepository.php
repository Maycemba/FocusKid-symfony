<?php

namespace App\Repository;

use App\Entity\LoginAnomaly;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class LoginAnomalyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LoginAnomaly::class);
    }

    /**
     * Count failed attempts from a specific IP since a given time.
     */
    public function countRecentByIp(string $ipAddress, \DateTimeInterface $since): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.ipAddress = :ip')
            ->andWhere('a.detectedAt >= :since')
            ->setParameter('ip', $ipAddress)
            ->setParameter('since', $since)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Count unique usernames tried from a specific IP since a given time.
     */
    public function countUniqueUsernamesByIp(string $ipAddress, \DateTimeInterface $since): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(DISTINCT a.username)')
            ->where('a.ipAddress = :ip')
            ->andWhere('a.detectedAt >= :since')
            ->setParameter('ip', $ipAddress)
            ->setParameter('since', $since)
            ->getQuery()
            ->getSingleScalarResult();
    }
}