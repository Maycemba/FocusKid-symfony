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
     * R1 — Count failed attempts from one IP in the last $minutes minutes.
     */
    public function countRecentFailuresByIp(string $ip, int $minutes = 10): int
    {
        $since = new \DateTime("-{$minutes} minutes");

        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.ipAddress = :ip')
            ->andWhere('a.detectedAt >= :since')
            ->andWhere('a.reason LIKE :pattern')
            ->setParameter('ip', $ip)
            ->setParameter('since', $since)
            ->setParameter('pattern', '__RAW__%')          // raw failure rows
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * R3 — Count DISTINCT usernames tried from one IP in the last $minutes minutes.
     * We fetch the raw rows and deduplicate in PHP (avoids DISTINCT on substring in DQL).
     */
    public function countDistinctUsernamesFromIp(string $ip, int $minutes = 10): int
    {
        $since = new \DateTime("-{$minutes} minutes");

        $rows = $this->createQueryBuilder('a')
            ->select('a.username')
            ->where('a.ipAddress = :ip')
            ->andWhere('a.detectedAt >= :since')
            ->andWhere('a.reason LIKE :pattern')
            ->setParameter('ip', $ip)
            ->setParameter('since', $since)
            ->setParameter('pattern', '__RAW__%')
            ->getQuery()
            ->getResult();

        return count(array_unique(array_column($rows, 'username')));
    }

    /**
     * Dedup check — has this IP already triggered this rule recently?
     * Uses first 10 chars of reason as a "rule tag" (e.g. "🔴 R1 — 5").
     */
    public function existsRecentAlert(string $ip, string $ruleTag, int $minutes = 10): bool
    {
        $since = new \DateTime("-{$minutes} minutes");

        $count = (int) $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.ipAddress = :ip')
            ->andWhere('a.detectedAt >= :since')
            ->andWhere('a.reason LIKE :tag')
            ->setParameter('ip', $ip)
            ->setParameter('since', $since)
            ->setParameter('tag', $ruleTag . '%')
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    /**
     * Dashboard — recent real alerts (not raw failure rows), newest first.
     */
    public function findRecentAlerts(int $limit = 100): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.reason NOT LIKE :raw')
            ->setParameter('raw', '__RAW__%')
            ->orderBy('a.detectedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Dashboard stats — how many times each rule fired.
     */
    public function getStats(): array
    {
        $stats = ['R1' => 0, 'R2' => 0, 'R3' => 0, 'total' => 0];

        $rows = $this->createQueryBuilder('a')
            ->select('a.reason, COUNT(a.id) as cnt')
            ->where('a.reason NOT LIKE :raw')
            ->setParameter('raw', '__RAW__%')
            ->groupBy('a.reason')
            ->getQuery()
            ->getResult();

        foreach ($rows as $r) {
            if (str_contains($r['reason'], 'R1')) { $stats['R1'] += $r['cnt']; }
            if (str_contains($r['reason'], 'R2')) { $stats['R2'] += $r['cnt']; }
            if (str_contains($r['reason'], 'R3')) { $stats['R3'] += $r['cnt']; }
            $stats['total'] += $r['cnt'];
        }

        return $stats;
    }
}