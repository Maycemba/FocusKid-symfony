<?php

namespace App\Service;

use App\Repository\HumeurJournaliereRepository;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Common\Entity\Style\Color;
use OpenSpout\Common\Entity\Style\CellAlignment;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HumeurExportService
{
    /**
     * Palette de couleurs par émotion (fond pastel + texte sombre)
     */
    private const EMOTION_COLORS = [
        'joyeux'      => ['bg' => 'FFD6E7', 'fg' => '8B004B'],
        'joyeuse'     => ['bg' => 'FFD6E7', 'fg' => '8B004B'],
        'heureux'     => ['bg' => 'FFD6E7', 'fg' => '8B004B'],
        'heureuse'    => ['bg' => 'FFD6E7', 'fg' => '8B004B'],
        'content'     => ['bg' => 'FFF3B0', 'fg' => '7A5F00'],
        'contente'    => ['bg' => 'FFF3B0', 'fg' => '7A5F00'],
        'excite'      => ['bg' => 'FFD59E', 'fg' => '7A3500'],
        'calme'       => ['bg' => 'C8F7DC', 'fg' => '1A5C36'],
        'serein'      => ['bg' => 'C8F7DC', 'fg' => '1A5C36'],
        'sereine'     => ['bg' => 'C8F7DC', 'fg' => '1A5C36'],
        'fier'        => ['bg' => 'D4EDDA', 'fg' => '155724'],
        'fiere'       => ['bg' => 'D4EDDA', 'fg' => '155724'],
        'amour'       => ['bg' => 'FFBBCC', 'fg' => '8B0026'],
        'triste'      => ['bg' => 'BDD7FF', 'fg' => '002F7A'],
        'tristesse'   => ['bg' => 'BDD7FF', 'fg' => '002F7A'],
        'colere'      => ['bg' => 'FFBDBD', 'fg' => '7A0000'],
        'enerve'      => ['bg' => 'FFBDBD', 'fg' => '7A0000'],
        'enervee'     => ['bg' => 'FFBDBD', 'fg' => '7A0000'],
        'peur'        => ['bg' => 'E8D5F5', 'fg' => '4A0080'],
        'effraye'     => ['bg' => 'E8D5F5', 'fg' => '4A0080'],
        'effrayee'    => ['bg' => 'E8D5F5', 'fg' => '4A0080'],
        'anxieux'     => ['bg' => 'E8D5F5', 'fg' => '4A0080'],
        'anxieuse'    => ['bg' => 'E8D5F5', 'fg' => '4A0080'],
        'stresse'     => ['bg' => 'F5D5E8', 'fg' => '6B003A'],
        'stressee'    => ['bg' => 'F5D5E8', 'fg' => '6B003A'],
        'fatigue'     => ['bg' => 'D6D6D6', 'fg' => '3C3C3C'],
        'fatiguee'    => ['bg' => 'D6D6D6', 'fg' => '3C3C3C'],
        'ennuye'      => ['bg' => 'E0E0E0', 'fg' => '444444'],
        'ennuyee'     => ['bg' => 'E0E0E0', 'fg' => '444444'],
        'surpris'     => ['bg' => 'FFF0C8', 'fg' => '664D00'],
        'surprise'    => ['bg' => 'FFF0C8', 'fg' => '664D00'],
        'confus'      => ['bg' => 'FFE8CC', 'fg' => '7A4400'],
        'confuse'     => ['bg' => 'FFE8CC', 'fg' => '7A4400'],
        'degout'      => ['bg' => 'D5EEC8', 'fg' => '2D4A1A'],
        'degoute'     => ['bg' => 'D5EEC8', 'fg' => '2D4A1A'],
        'degoutee'    => ['bg' => 'D5EEC8', 'fg' => '2D4A1A'],
    ];

    private const DEFAULT_COLOR = ['bg' => 'F0F0F0', 'fg' => '444444'];

    private array $emotionStyleCache = [];
    private array $autoPaletteCache  = [];

    public function __construct(
        private HumeurJournaliereRepository $repo
    ) {}

    public function exportExcel(
        ?string $dateDebut = null,
        ?string $dateFin   = null,
        ?string $emotion   = null
    ): StreamedResponse {

        $humeurs  = $this->repo->findForExport($dateDebut, $dateFin, $emotion);
        $filename = 'humeurs_' . date('Ymd_His') . '.xlsx';
        $tmpFile  = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename;

        // ── Styles fixes ───────────────────────────────────────────────────

        $styleHeader = (new Style())
            ->setFontBold()->setFontSize(11)
            ->setFontColor(Color::WHITE)
            ->setBackgroundColor('3A0CA3')
            ->setCellAlignment(CellAlignment::CENTER);

        $styleTitre = (new Style())
            ->setFontBold()->setFontSize(13)
            ->setFontColor('3A0CA3')
            ->setBackgroundColor('EDE9FF')
            ->setCellAlignment(CellAlignment::CENTER);

        $styleSousTitre = (new Style())
            ->setFontItalic()->setFontSize(9)
            ->setFontColor('888888')
            ->setCellAlignment(CellAlignment::CENTER);

        $styleTotal = (new Style())
            ->setFontBold()->setFontSize(10)
            ->setFontColor('3A0CA3')
            ->setBackgroundColor('EDE9FF');

        $styleSectionTitle = (new Style())
            ->setFontBold()->setFontSize(11)
            ->setFontColor('FFFFFF')
            ->setBackgroundColor('7209B7')
            ->setCellAlignment(CellAlignment::CENTER);

        $styleStatHeader = (new Style())
            ->setFontBold()->setFontSize(10)
            ->setFontColor('FFFFFF')
            ->setBackgroundColor('560BAD')
            ->setCellAlignment(CellAlignment::CENTER);

        $styleDayHeader = (new Style())
            ->setFontBold()->setFontSize(10)
            ->setFontColor('FFFFFF')
            ->setBackgroundColor('2D6A4F')
            ->setCellAlignment(CellAlignment::CENTER);

        $styleDayRow    = (new Style())->setFontSize(10)->setBackgroundColor('D8F3DC');
        $styleDayRowAlt = (new Style())->setFontSize(10)->setBackgroundColor('F0FBF1');

        // ── Pré-calcul des stats ───────────────────────────────────────────

        $statsMap = [];
        foreach ($humeurs as $h) {
            $nom = $h->getEmotion()?->getNom() ?? 'Inconnue';
            $statsMap[$nom] = ($statsMap[$nom] ?? 0) + 1;
        }
        arsort($statsMap);
        $total = count($humeurs);

        $jours = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];

        $dayMap = [];
        foreach ($humeurs as $h) {
            $dt = $h->getDateHeure();
            if ($dt) {
                $key = $dt->format('Y-m-d');
                $dayMap[$key] = ($dayMap[$key] ?? 0) + 1;
            }
        }
        ksort($dayMap);

        $periode = 'Exporté le ' . date('d/m/Y à H:i');
        if ($dateDebut || $dateFin) {
            $periode .= '  |  Période : ' . ($dateDebut ?: '…') . ' → ' . ($dateFin ?: '…');
        }
        if ($emotion) {
            $periode .= '  |  Émotion filtrée : ' . $emotion;
        }

        // ── Writer XLSX ───────────────────────────────────────────────────
        $writer = new Writer();
        $writer->openToFile($tmpFile);

        // ════════════════════════════════════════════════════════════════
        // FEUILLE 1 : Données + Statistiques complètes
        // ════════════════════════════════════════════════════════════════
        $writer->getCurrentSheet()->setName('Humeurs & Stats');

        $writer->addRow($this->makeRow(['RAPPORT DES HUMEURS JOURNALIÈRES — FocusKid', '', '', '', '', ''], $styleTitre));
        $writer->addRow($this->makeRow([$periode, '', '', '', '', ''], $styleSousTitre));
        $writer->addRow($this->makeRow(['', '', '', '', '', '']));

        // -- Stats par émotion --
        $writer->addRow($this->makeRow(['▌ STATISTIQUES PAR ÉMOTION', '', '', '', '', ''], $styleSectionTitle));
        $writer->addRow($this->makeRow(['Émotion', 'Occurrences', 'Pourcentage', '', '', ''], $styleStatHeader));

        foreach ($statsMap as $nom => $count) {
            $pct   = $total > 0 ? round($count / $total * 100, 1) . ' %' : '0 %';
            $writer->addRow($this->makeRow([$nom, $count, $pct, '', '', ''], $this->getEmotionStyle($nom)));
        }
        $writer->addRow($this->makeRow(['TOTAL', $total, '100 %', '', '', ''], $styleTotal));

        // -- Stats par jour --
        $writer->addRow($this->makeRow(['', '', '', '', '', '']));
        $writer->addRow($this->makeRow(['▌ STATISTIQUES PAR JOUR', '', '', '', '', ''], $styleSectionTitle));
        $writer->addRow($this->makeRow(['Date', 'Jour de semaine', 'Nombre d\'humeurs', '', '', ''], $styleDayHeader));

        $di = 0;
        foreach ($dayMap as $date => $cnt) {
            $dt    = new \DateTime($date);
            $style = ($di % 2 === 0) ? $styleDayRow : $styleDayRowAlt;
            $writer->addRow($this->makeRow([$dt->format('d/m/Y'), $jours[(int) $dt->format('w')], $cnt, '', '', ''], $style));
            $di++;
        }

        // -- Liste complète --
        $writer->addRow($this->makeRow(['', '', '', '', '', '']));
        $writer->addRow($this->makeRow(['▌ LISTE COMPLÈTE DES HUMEURS', '', '', '', '', ''], $styleSectionTitle));
        $writer->addRow($this->makeRow(['#', 'Date', 'Heure', 'Jour de semaine', 'Émotion', ''], $styleHeader));

        foreach ($humeurs as $h) {
            $dt          = $h->getDateHeure();
            $jourSemaine = $dt ? $jours[(int) $dt->format('w')] : '';
            $nomEmotion  = $h->getEmotion()?->getNom() ?? '—';
            $writer->addRow($this->makeRow([
                $h->getId(),
                $dt ? $dt->format('d/m/Y') : '',
                $dt ? $dt->format('H:i')   : '',
                $jourSemaine,
                $nomEmotion,
                '',
            ], $this->getEmotionStyle($nomEmotion)));
        }
        $writer->addRow($this->makeRow(['TOTAL', count($humeurs) . ' enregistrement(s)', '', '', '', ''], $styleTotal));

        // ════════════════════════════════════════════════════════════════
        // FEUILLE 2 : Stats par émotion (avec couleurs)
        // ════════════════════════════════════════════════════════════════
        $writer->addNewSheetAndMakeItCurrent()->setName('Stats par émotion');

        $writer->addRow($this->makeRow(['STATISTIQUES PAR ÉMOTION', '', ''], $styleTitre));
        $writer->addRow($this->makeRow(['', '', '']));
        $writer->addRow($this->makeRow(['Émotion', 'Nombre', 'Pourcentage'], $styleStatHeader));

        foreach ($statsMap as $nom => $count) {
            $pct = $total > 0 ? round($count / $total * 100, 1) . ' %' : '0 %';
            $writer->addRow($this->makeRow([$nom, $count, $pct], $this->getEmotionStyle($nom)));
        }
        $writer->addRow($this->makeRow(['TOTAL', $total, '100 %'], $styleTotal));

        // ════════════════════════════════════════════════════════════════
        // FEUILLE 3 : Stats par jour
        // ════════════════════════════════════════════════════════════════
        $writer->addNewSheetAndMakeItCurrent()->setName('Stats par jour');

        $writer->addRow($this->makeRow(['STATISTIQUES PAR JOUR', '', ''], $styleTitre));
        $writer->addRow($this->makeRow(['', '', '']));
        $writer->addRow($this->makeRow(['Date', 'Jour de semaine', 'Nombre'], $styleDayHeader));

        $di = 0;
        foreach ($dayMap as $date => $cnt) {
            $dt    = new \DateTime($date);
            $style = ($di % 2 === 0) ? $styleDayRow : $styleDayRowAlt;
            $writer->addRow($this->makeRow([$dt->format('d/m/Y'), $jours[(int) $dt->format('w')], $cnt], $style));
            $di++;
        }

        // ════════════════════════════════════════════════════════════════
        // FEUILLE 4 : Données brutes (avec couleurs par émotion)
        // ════════════════════════════════════════════════════════════════
        $writer->addNewSheetAndMakeItCurrent()->setName('Données brutes');

        $writer->addRow($this->makeRow(['LISTE COMPLÈTE DES HUMEURS', '', '', '', ''], $styleTitre));
        $writer->addRow($this->makeRow([$periode, '', '', '', ''], $styleSousTitre));
        $writer->addRow($this->makeRow(['', '', '', '', '']));
        $writer->addRow($this->makeRow(['#', 'Date', 'Heure', 'Jour de semaine', 'Émotion'], $styleHeader));

        foreach ($humeurs as $h) {
            $dt          = $h->getDateHeure();
            $jourSemaine = $dt ? $jours[(int) $dt->format('w')] : '';
            $nomEmotion  = $h->getEmotion()?->getNom() ?? '—';
            $writer->addRow($this->makeRow([
                $h->getId(),
                $dt ? $dt->format('d/m/Y') : '',
                $dt ? $dt->format('H:i')   : '',
                $jourSemaine,
                $nomEmotion,
            ], $this->getEmotionStyle($nomEmotion)));
        }
        $writer->addRow($this->makeRow(['TOTAL', count($humeurs) . ' enregistrement(s)', '', '', ''], $styleTotal));

        $writer->close();

        $filesize = filesize($tmpFile);
        $response = new StreamedResponse(function () use ($tmpFile) {
            readfile($tmpFile);
            @unlink($tmpFile);
        });

        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');
        $response->headers->set('Cache-Control', 'max-age=0');
        $response->headers->set('Content-Length', (string) $filesize);

        return $response;
    }

    private function getEmotionStyle(string $nom): Style
    {
        // Normalise : minuscules + retire accents (iconv peut ne pas être dispo, on fait simple)
        $key = mb_strtolower(trim($nom));
        $keyNorm = $this->normalizeForKey($key);

        $cacheKey = $keyNorm;
        if (isset($this->emotionStyleCache[$cacheKey])) {
            return $this->emotionStyleCache[$cacheKey];
        }

        $colors = self::EMOTION_COLORS[$key]
            ?? self::EMOTION_COLORS[$keyNorm]
            ?? $this->getAutoColor($keyNorm);

        $style = (new Style())
            ->setFontSize(10)
            ->setFontColor($colors['fg'])
            ->setBackgroundColor($colors['bg']);

        $this->emotionStyleCache[$cacheKey] = $style;
        return $style;
    }

    private function normalizeForKey(string $s): string
    {
        $map = [
            'é'=>'e','è'=>'e','ê'=>'e','ë'=>'e',
            'à'=>'a','â'=>'a','ä'=>'a',
            'î'=>'i','ï'=>'i',
            'ô'=>'o','ö'=>'o',
            'ù'=>'u','û'=>'u','ü'=>'u',
            'ç'=>'c','ñ'=>'n',
        ];
        return strtr($s, $map);
    }

    private function getAutoColor(string $nom): array
    {
        if (isset($this->autoPaletteCache[$nom])) {
            return $this->autoPaletteCache[$nom];
        }
        $palette = [
            ['bg' => 'FFE8D6', 'fg' => '7A3500'],
            ['bg' => 'D6E8FF', 'fg' => '003580'],
            ['bg' => 'E8FFD6', 'fg' => '1A5C00'],
            ['bg' => 'FFD6F5', 'fg' => '7A006B'],
            ['bg' => 'D6FFF5', 'fg' => '006B5C'],
            ['bg' => 'FFF5D6', 'fg' => '7A5C00'],
            ['bg' => 'F5D6FF', 'fg' => '5C006B'],
            ['bg' => 'D6F5FF', 'fg' => '00456B'],
            ['bg' => 'FFD6D6', 'fg' => '7A0000'],
            ['bg' => 'D6FFD6', 'fg' => '004400'],
        ];
        $colors = $palette[abs(crc32($nom)) % count($palette)];
        $this->autoPaletteCache[$nom] = $colors;
        return $colors;
    }

    private function makeRow(array $values, ?Style $style = null): Row
    {
        $cells = [];
        foreach ($values as $value) {
            $cell = match (true) {
                is_int($value)   => Cell::fromValue($value),
                is_float($value) => Cell::fromValue($value),
                default          => Cell::fromValue((string) $value),
            };
            if ($style !== null) {
                $cell->setStyle($style);
            }
            $cells[] = $cell;
        }
        return new Row($cells);
    }
}