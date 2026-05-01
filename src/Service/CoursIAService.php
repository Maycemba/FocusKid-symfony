<?php

namespace App\Service;

/**
 * Moteur d'inférence TF-IDF + Régression Logistique en PHP pur.
 * Charge le modèle exporté depuis Python (sklearn) et fait les prédictions.
 * Réponses optimisées pour les enfants TDAH : courtes, dynamiques, engageantes.
 */
class CoursIAService
{
    private array $model;
    private bool  $loaded = false;

    // ─── Réponses TDAH par intention + matière ─────────────────────────────────
    private array $reponses = [
        'titre' => [
            'mathematiques' => [
                '🚀 Mission : bats le record des chiffres !',
                '🧩 Le défi secret des nombres — tu peux le relever ?',
                '⚡ Calcule plus vite que ta calculatrice !',
                '🎯 Chasseur de formes : trouve-les toutes !',
                '🏆 Tournoi des maths — qui sera le champion ?',
            ],
            'francais' => [
                '🕵️ Détective des mots — l\'enquête commence !',
                '🦸 Super-lecteur : décode l\'histoire cachée !',
                '🎭 Deviens le maître des mots magiques !',
                '🔥 Écris une histoire en 10 minutes — chrono !',
                '🗝️ Le code secret des lettres — à toi de jouer !',
            ],
            'sciences' => [
                '🔬 Apprenti savant : l\'expérience t\'attend !',
                '🌍 Explore la planète comme un explorateur !',
                '⚗️ La potion mystère — tu oses la créer ?',
                '🦕 Les secrets de la nature — mission découverte !',
                '🌋 Scientifique en herbe : le défi du jour !',
            ],
            'histoire' => [
                '⚔️ Voyage dans le temps — prêt pour l\'aventure ?',
                '🏰 Chevalier, pharaon ou explorateur — choisis !',
                '🗺️ La grande chasse au trésor de l\'histoire !',
                '👑 Tu aurais survécu au Moyen Âge ?',
                '🚀 Remonte le temps : mission top secrète !',
            ],
            'anglais' => [
                '🌍 Parle anglais en 5 minutes — pari lancé !',
                '🎮 English Quest : joue et apprends en même temps !',
                '🦸 Super-héros de l\'anglais — ta mission commence !',
                '🎵 Les mots anglais qui font boom dans ta tête !',
                '🕹️ Niveau 1 : débloque l\'anglais aujourd\'hui !',
            ],
            'default' => [
                '🚀 La mission du jour — acceptes-tu le défi ?',
                '🧠 Cerveau en action : prêt à tout déchirer ?',
                '⚡ 3, 2, 1… l\'aventure commence maintenant !',
                '🏆 Champion en devenir — c\'est toi aujourd\'hui !',
                '🎯 Le secret que tu vas découvrir aujourd\'hui !',
            ],
        ],

        'description' => [
            'mathematiques' => [
                '🚀 Tu vas relever des défis de calcul comme un vrai champion ! Chaque exercice est une mission à accomplir. Prêt à battre ton record ?',
                '🧩 Aujourd\'hui tu pars à la chasse aux formes et aux nombres cachés ! Des jeux, des défis express et des surprises t\'attendent à chaque étape.',
                '⚡ En 3 étapes ultra-rapides, tu vas maîtriser les maths comme jamais ! Des puzzles, des chronos et des challenges pour ne jamais t\'ennuyer.',
                '🎯 C\'est TON tournoi des maths ! Tu joues, tu gagnes des points et tu deviens le boss des chiffres. Chaque bonne réponse t\'amène plus loin.',
            ],
            'francais' => [
                '🕵️ Tu es le détective ! Des mots mystérieux t\'attendent, des histoires à déchiffrer et des énigmes à résoudre. L\'enquête commence maintenant !',
                '🦸 Transforme-toi en super-lecteur en moins d\'une heure ! Des histoires folles, des jeux de mots et des défis d\'écriture rien que pour toi.',
                '🎭 Tu vas écrire, jouer et inventer aujourd\'hui ! Des activités en rafale pour que ton cerveau reste en feu du début à la fin.',
            ],
            'sciences' => [
                '🔬 Enfile ta blouse de savant et prépare-toi à faire des expériences dingues ! Tu vas observer, tester et découvrir des trucs de ouf.',
                '🌍 Tu pars en exploration aujourd\'hui ! Des mystères de la nature à percer, des expériences à réaliser et des questions auxquelles toi seul peux répondre.',
                '⚗️ Mission scientifique acceptée ! Des manipulations, des observations et des défis express pour que tu apprennes sans t\'en rendre compte.',
            ],
            'default' => [
                '🚀 C\'est parti pour une aventure d\'apprentissage ! Des défis courts, des activités fun et des surprises à chaque étape — tu ne vois pas le temps passer.',
                '⚡ Prêt pour le défi du jour ? Des missions rapides, des jeux et des challenges t\'attendent. Chaque étape franchie te rend plus fort !',
                '🎯 Aujourd\'hui tu deviens expert ! Des activités pensées spécialement pour toi : rapides, fun et toujours variées pour garder ton cerveau en éveil.',
            ],
        ],

        'objectif' => [
            'mathematiques' => [
                '⚡ Résoudre 5 défis de calcul mental en moins de 3 minutes',
                '🧩 Reconnaître et nommer toutes les formes géométriques de base',
                '🎯 Appliquer les 4 opérations dans des situations concrètes et fun',
                '🏆 Compléter une mission de résolution de problèmes sans aide',
                '🚀 Utiliser les fractions dans un jeu réel de partage',
            ],
            'francais' => [
                '🕵️ Lire un texte court et retrouver les 5 informations cachées',
                '✍️ Écrire une histoire de 5 phrases avec début, milieu et fin',
                '🎯 Conjuguer 10 verbes en un temps record — chrono lancé !',
                '🦸 Inventer 3 phrases originales avec des mots nouveaux',
                '🗝️ Identifier les erreurs dans un texte comme un vrai correcteur',
            ],
            'default' => [
                '🚀 Réussir le défi principal de la séance en autonomie',
                '⚡ Participer activement à au moins 2 activités de groupe',
                '🎯 Expliquer à un camarade ce qu\'on a appris aujourd\'hui',
                '🧠 Compléter sa fiche-mission sans regarder le cours',
                '🏆 Progresser d\'au moins un niveau par rapport à la dernière fois',
            ],
        ],

        'plan' => [
            'default' => [
                '⚡ 1. DÉMARRAGE BOOST (5 min) — défi express ou question mystère pour allumer le cerveau',
                '🎯 2. MISSION PRINCIPALE (10 min) — découverte du concept en mode jeu ou histoire',
                '🤝 3. DÉFI EN ÉQUIPE (10 min) — activité courte à 2 ou en petit groupe',
                '💪 4. À TOI DE JOUER (10 min) — exercices individuels rapides avec timer visible',
                '🏆 5. BILAN CHAMPION (5 min) — chaque élève dit 1 chose apprise + mini-célébration',
            ],
        ],

        'activite' => [
            'mathematiques' => [
                '⚡ Calcul mental chrono : 10 opérations en 2 minutes — qui fait le meilleur score ?',
                '🃏 Battle de cartes mathématiques : chaque carte = un défi à relever',
                '🏃 Maths en mouvement : coller la bonne réponse sur le mur en courant',
                '🧩 Puzzle géant : reconstituer la figure géométrique en équipe',
                '🎯 Vrai/Faux express : lever la main droite ou gauche selon la réponse',
            ],
            'francais' => [
                '🎭 Théâtre éclair : jouer la scène du texte en 3 minutes top chrono',
                '🔤 Mot mystère : deviner le mot en 5 indices — le premier qui trouve gagne !',
                '✍️ Défi d\'écriture : une histoire en 5 phrases, 4 minutes, c\'est parti !',
                '🕵️ Chasse aux erreurs : trouver les 5 fautes cachées dans le texte',
                '🎵 Rap conjugaison : inventer un rap pour retenir les formes verbales',
            ],
            'default' => [
                '⚡ Défi express en binôme : résoudre le problème avant le timer (3 min)',
                '🎯 Quiz debout : bonne réponse = reste debout, mauvaise = assis — dernier debout gagne !',
                '🏃 Mission post-it : coller sa réponse au bon endroit sur le tableau',
                '🧠 Explique à ton voisin : tu l\'as compris si tu peux l\'expliquer en 1 minute',
                '🏆 Mini-tournoi par équipe : 5 questions, 5 points, le meilleur score gagne',
            ],
        ],

        'tdah' => [
            'default' => [
                "🧠 **Stratégies TDAH testées et approuvées :**\n\n"
                . "⏱️ **Blocs courts** — Séquences de 10 min max avec mini-pause de 2 min\n"
                . "🎯 **1 seule consigne à la fois** — Jamais plus d'une instruction, toujours affichée\n"
                . "⚡ **Timer visible** — Sablier ou minuterie à l'écran pour structurer chaque activité\n"
                . "🌈 **Code couleur** — Une couleur = une étape, ça ancre dans le cerveau\n"
                . "🏆 **Micro-récompenses** — Fêter chaque petite victoire immédiatement\n"
                . "🤸 **Pause active** — 2 min de mouvement (étirement, saut) toutes les 20 min\n"
                . "🎮 **Gamifier** — Points, niveaux, défis : transformer chaque tâche en jeu\n"
                . "📍 **Ancrage sensoriel** — Manipuler, colorier, coller : apprendre avec les mains",
            ],
        ],

        'aide' => [
            'default' => [
                "👋 **Bienvenue dans l'assistant FocusKids TDAH !**\n\n"
                . "Je génère du contenu **spécialement conçu pour capter l'attention des enfants TDAH** :\n\n"
                . "💡 **Titres** — Courts, dynamiques, avec un défi ou une aventure\n"
                . "📝 **Descriptions** — 2-3 phrases max, qui parlent directement à l'enfant\n"
                . "🎯 **Objectifs** — Concrets, mesurables, formulés comme des missions\n"
                . "📚 **Plan** — Séquences courtes avec pauses et variété\n"
                . "🎮 **Activités** — Toujours fun, courtes et avec du mouvement\n"
                . "🧠 **Conseils TDAH** — Stratégies pédagogiques adaptées\n\n"
                . "💬 Posez-moi une question ou utilisez les boutons rapides !",
            ],
        ],
    ];

    public function __construct(private string $modelPath)
    {
    }

    public function repondre(string $question, array $context = []): string
    {
        $this->chargerModele();

        [$intention, $confiance] = $this->predire($question);

        $matiere = $this->detecterMatiere(
            ($context['titre'] ?? '') . ' ' . ($context['description'] ?? '')
        );

        return $this->genererReponse($intention, $matiere, $confiance);
    }

    private function predire(string $texte): array
    {
        $vecteur = $this->tfidfVectorize($texte);

        $classes = $this->model['classes'];
        $scores  = [];

        foreach ($classes as $i => $classe) {
            $score = $this->model['intercept'][$i];
            foreach ($vecteur as $j => $val) {
                $score += $this->model['coef'][$i][$j] * $val;
            }
            $scores[$classe] = $score;
        }

        $probas = $this->softmax(array_values($scores));
        $maxIdx = array_keys($probas, max($probas))[0];

        return [$classes[$maxIdx], $probas[$maxIdx]];
    }

    private function tfidfVectorize(string $texte): array
    {
        $vocab  = $this->model['vocabulary'];
        $idf    = $this->model['idf'];
        $nGrams = $this->extraireNgrams($texte, 1, 2);

        $tf = [];
        foreach ($nGrams as $gram) {
            $tf[$gram] = ($tf[$gram] ?? 0) + 1;
        }

        $vecteur = array_fill(0, count($vocab), 0.0);

        foreach ($tf as $gram => $freq) {
            if (!isset($vocab[$gram])) continue;
            $idx = $vocab[$gram];
            $tfVal = 1.0 + log($freq);
            $vecteur[$idx] = $tfVal * $idf[$idx];
        }

        $norme = sqrt(array_sum(array_map(fn($v) => $v * $v, $vecteur)));
        if ($norme > 0) {
            $vecteur = array_map(fn($v) => $v / $norme, $vecteur);
        }

        return $vecteur;
    }

    private function extraireNgrams(string $texte, int $min, int $max): array
    {
        $texte = $this->normaliser(strtolower($texte));
        $mots  = preg_split('/\s+/', trim($texte));
        $grams = [];

        for ($n = $min; $n <= $max; $n++) {
            for ($i = 0; $i <= count($mots) - $n; $i++) {
                $grams[] = implode(' ', array_slice($mots, $i, $n));
            }
        }

        return $grams;
    }

    private function softmax(array $scores): array
    {
        $max   = max($scores);
        $exps  = array_map(fn($s) => exp($s - $max), $scores);
        $somme = array_sum($exps);
        return array_map(fn($e) => $e / $somme, $exps);
    }

    private function detecterMatiere(string $texte): string
    {
        $t = strtolower($this->normaliser($texte));

        $matieres = [
            'mathematiques' => ['math', 'calcul', 'nombre', 'geometrie', 'algebre', 'fraction', 'equation', 'chiffre', 'operation'],
            'francais'      => ['francais', 'lecture', 'grammaire', 'orthographe', 'conjugaison', 'vocabulaire', 'redaction', 'ecriture', 'mot'],
            'sciences'      => ['science', 'biologie', 'physique', 'chimie', 'nature', 'vivant', 'experience', 'corps', 'plante'],
            'histoire'      => ['histoire', 'geographie', 'chronologie', 'civilisation', 'guerre', 'revolution', 'moyen age', 'pharaon'],
            'anglais'       => ['anglais', 'english', 'vocabulary', 'grammar', 'speaking', 'mot anglais'],
        ];

        $best = 'default';
        $max  = 0;

        foreach ($matieres as $matiere => $mots) {
            $score = 0;
            foreach ($mots as $mot) {
                $score += substr_count($t, $mot);
            }
            if ($score > $max) {
                $max  = $score;
                $best = $matiere;
            }
        }

        return $best;
    }

    private function genererReponse(string $intention, string $matiere, float $confiance): string
    {
        $pool  = $this->reponses[$intention] ?? $this->reponses['aide'];
        $liste = $pool[$matiere] ?? $pool['default'] ?? $pool[array_key_first($pool)];

        $intros = [
            'titre'       => "💡 **Titres accrocheurs pour enfants TDAH** — clique pour utiliser :",
            'description' => "📝 **Descriptions engageantes** — courtes et dynamiques :",
            'objectif'    => "🎯 **Objectifs** formulés comme des missions :",
            'plan'        => "📚 **Plan de séance** adapté TDAH — blocs courts et rythmés :",
            'activite'    => "🎮 **Activités anti-ennui** — rapides, fun et dynamisantes :",
            'tdah'        => "",
            'aide'        => "",
        ];

        if (in_array($intention, ['tdah', 'aide'])) {
            return $liste[0];
        }

        $intro   = $intros[$intention] ?? "Voici des suggestions :";
        $items   = array_slice($liste, 0, 5);
        $contenu = implode("\n", array_map(fn($i) => "• $i", $items));

        return "$intro\n\n$contenu";
    }

    private function chargerModele(): void
    {
        if ($this->loaded) return;

        if (!file_exists($this->modelPath)) {
            throw new \RuntimeException("Modèle IA introuvable : {$this->modelPath}");
        }

        $json = file_get_contents($this->modelPath);
        $this->model = json_decode($json, true);

        if (!$this->model) {
            throw new \RuntimeException("Modèle IA invalide (JSON corrompu).");
        }

        $this->loaded = true;
    }

    private function normaliser(string $texte): string
    {
        $map = ['é'=>'e','è'=>'e','ê'=>'e','ë'=>'e','à'=>'a','â'=>'a','ä'=>'a',
                'î'=>'i','ï'=>'i','ô'=>'o','ö'=>'o','ù'=>'u','û'=>'u','ü'=>'u',
                'ç'=>'c','É'=>'e','È'=>'e','Ê'=>'e','À'=>'a','Â'=>'a','Î'=>'i',
                'Ô'=>'o','Û'=>'u','Ù'=>'u','Ç'=>'c'];
        return strtr($texte, $map);
    }
}
