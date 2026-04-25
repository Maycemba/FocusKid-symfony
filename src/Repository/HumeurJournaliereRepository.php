<?php

namespace App\Repository;

use App\Entity\HumeurJournaliere;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HumeurJournaliere>
 */
class HumeurJournaliereRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HumeurJournaliere::class);
    }

    /**
     * Statistiques : compte par émotion pour le pie chart
     */
    public function countByEmotion(): array
    {
        return $this->createQueryBuilder('h')
            ->select('e.nom AS nom, e.id AS emotionId, COUNT(h.id) AS total')
            ->leftJoin('h.emotion', 'e')
            ->groupBy('e.id')
            ->orderBy('total', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Statistiques : humeurs par jour pour la courbe (30 derniers jours)
     */
    public function countByDay(): array
    {
        $since = new \DateTime('-30 days');

        $humeurs = $this->createQueryBuilder('h')
            ->leftJoin('h.emotion', 'e')
            ->addSelect('e')
            ->where('h.dateHeure >= :since')
            ->setParameter('since', $since)
            ->orderBy('h.dateHeure', 'ASC')
            ->getQuery()
            ->getResult();

        // Regroupement en PHP : [jour][nomEmotion] => count
        $grouped = [];
        foreach ($humeurs as $h) {
            $jour = $h->getDateHeure() ? $h->getDateHeure()->format('Y-m-d') : 'Inconnu';
            $nom  = $h->getEmotion() ? $h->getEmotion()->getNom() : 'Inconnu';

            if (!isset($grouped[$jour][$nom])) {
                $grouped[$jour][$nom] = 0;
            }
            $grouped[$jour][$nom]++;
        }

        // Aplatir en tableau [{jour, nom, total}]
        $result = [];
        foreach ($grouped as $jour => $emotions) {
            foreach ($emotions as $nom => $total) {
                $result[] = [
                    'jour'  => $jour,
                    'nom'   => $nom,
                    'total' => $total,
                ];
            }
        }

        return $result;
    }
}