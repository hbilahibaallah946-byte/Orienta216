<?php

namespace App\Services;

use App\Models\EtudiantProfile;
use App\Models\Filiere;
use App\Models\Moyenne;
use App\Models\Recommandation;
use App\Models\User;

class ProfilService
{
    /** Nombre de filières conservées par étudiant */
    private const TOP_N = 12;

    /** Pondération du score final (somme = 1) */
    private const W_ACADEMIQUE = 0.4;

    private const W_COMPATIBILITE = 0.3;

    private const W_COMPETITIVITE = 0.3;

    private const ACADEMIQUE_NEUTRE = 50;
    private const COMPATIBILITE_NEUTRE = 50;

    private const COMPETITIVITE_NEUTRE = 25;

    public function buildProfile(User $etudiant): EtudiantProfile
    {
        $reponses = $etudiant->reponses()->with('question')->get();

        $interets    = [];
        $competences = [];
        $preferences = [];

        foreach ($reponses as $reponse) {
            $question = $reponse->question;

            if (! $question) {
                continue;
            }

            $questionTags = $this->extractQuestionTags($question);
            if ($questionTags === []) {
                continue;
            }

            if ($this->reponseEstPositive($reponse->reponse)) {
                $tags = $questionTags;

                match ($question->categorie ?? 'interet') {
                    'competence' => array_push($competences, ...$tags),
                    'preference' => array_push($preferences, ...$tags),
                    default      => array_push($interets, ...$tags),
                };
            }
        }

        $profile = EtudiantProfile::updateOrCreate(
            ['etudiant_id' => $etudiant->id],
            [
                'interets'    => array_values(array_unique($interets)),
                'competences' => array_values(array_unique($competences)),
                'preferences' => array_values(array_unique($preferences)),
            ]
        );

        $this->computeRecommandations($etudiant, $profile);

        return $profile;
    }

    /**
     * Score de compatibilité questionnaire (mots-clés profil vs critères filière).
     */
    public function scoreCompatibilite(EtudiantProfile $profile, Filiere $filiere): int
    {
        $criteres = array_map('mb_strtolower', (array) ($filiere->criteres ?? []));
        $profileTags = array_map('mb_strtolower', $profile->allTags());

        // Expand bandeau-level criteria into keyword sets for finer matching
        $expandedCriteres = $this->expandCriteresKeywords($criteres);

        if (empty($expandedCriteres) && empty($criteres)) {
            return self::COMPATIBILITE_NEUTRE;
        }

        if (empty($profileTags)) {
            return self::COMPATIBILITE_NEUTRE;
        }

        // Direct intersection between expanded criteria and profile tags
        $allCritereWords = array_unique(array_merge($criteres, $expandedCriteres));
        $matches = count(array_intersect($allCritereWords, $profileTags));
        $score = (int) round(($matches / max(1, count($allCritereWords))) * 100);

        // Bonus for competence-tag matches
        $competenceTags = array_map('mb_strtolower', (array) ($profile->competences ?? []));
        $competenceMatches = count(array_intersect($allCritereWords, $competenceTags));
        if ($competenceMatches > 0) {
            $score = min(100, $score + (int) round(($competenceMatches / max(1, count($allCritereWords))) * 15));
        }

        // Lexical matching (substring scan over filière text)
        $filiereText = $this->filiereTextePourCompat($filiere);
        $lexical = $this->scoreCompatibiliteLexicale($filiereText, $profileTags);
        $score = max($score, $lexical);

        // Dernier filet : évite un 0 % « bloquant » si le profil a des tags mais aucun alignement détecté
        if ($score < 5 && $profileTags !== []) {
            $score = (int) round(self::COMPATIBILITE_NEUTRE * 0.45);
        }

        return min(100, $score);
    }

    /**
     * Expand bandeau / criteria labels into keyword sets for finer matching.
     * e.g. "Lettres, langues et cycles préparatoires littéraires" → ['lettres', 'langues', 'littérature', ...]
     */
    private function expandCriteresKeywords(array $criteres): array
    {
        $bandeauKeywords = [
            'lettres, langues et cycles préparatoires littéraires' => ['lettres', 'langues', 'littérature', 'traduction', 'arabe', 'français', 'anglais', 'civilisation', 'philologie'],
            'sciences humaines, sociales, religieuses et de l\'éducation' => ['psychologie', 'sociologie', 'philosophie', 'histoire', 'géographie', 'éducation', 'pédagogie', 'anthropologie', 'religion'],
            'sciences juridiques et politiques' => ['droit', 'juridique', 'politique', 'loi', 'justice', 'avocat'],
            'économie et gestion' => ['économie', 'gestion', 'comptabilité', 'finance', 'commerce', 'marketing', 'management', 'banque'],
            'sciences fondamentales et appliquées' => ['mathématiques', 'physique', 'chimie', 'biologie', 'sciences', 'recherche', 'laboratoire'],
            'sciences médicales et pharmaceutiques' => ['médecine', 'pharmacie', 'santé', 'hôpital', 'chirurgie', 'dentaire', 'infirmier', 'biologie médicale'],
            'architecture et urbanisme' => ['architecture', 'urbanisme', 'bâtiment', 'construction', 'aménagement'],
            'arts et design' => ['arts', 'design', 'musique', 'peinture', 'sculpture', 'graphisme', 'créativité', 'cinéma', 'théâtre'],
            'sciences et techniques des activités physiques et sportives' => ['sport', 'éducation physique', 'athlétisme', 'entraînement'],
            'santé publique' => ['santé', 'hygiène', 'nutrition', 'prévention', 'épidémiologie'],
            'cycles préparatoires scientifiques' => ['préparatoire', 'ingénieur', 'sciences', 'mathématiques', 'physique', 'concours'],
            'sciences technologiques' => ['technologie', 'ingénierie', 'technique', 'mécanique', 'électrique', 'électronique'],
            'informatique et communications' => ['informatique', 'programmation', 'logiciel', 'réseau', 'télécommunications', 'data', 'intelligence artificielle'],
        ];

        $keywords = [];
        foreach ($criteres as $c) {
            $lower = mb_strtolower(trim($c));
            if (isset($bandeauKeywords[$lower])) {
                array_push($keywords, ...$bandeauKeywords[$lower]);
            }
            // Also try partial matching
            foreach ($bandeauKeywords as $label => $kws) {
                if (mb_strpos($lower, mb_substr($label, 0, 15)) !== false || mb_strpos($label, $lower) !== false) {
                    array_push($keywords, ...$kws);
                }
            }
        }

        return array_values(array_unique($keywords));
    }

    /**
     * Texte concaténé (spécialité, licence, critères, expanded keywords) pour recoupement avec les tags profil.
     */
    private function filiereTextePourCompat(Filiere $filiere): string
    {
        $parts = array_filter([
            (string) ($filiere->specialite ?? ''),
            (string) ($filiere->nom ?? ''),
            (string) ($filiere->licence ?? ''),
            (string) ($filiere->type_bac ?? ''),
        ]);

        $criteres = (array) ($filiere->criteres ?? []);
        foreach ($criteres as $c) {
            $parts[] = (string) $c;
        }

        // Also add expanded keywords from bandeau
        $expanded = $this->expandCriteresKeywords(array_map('mb_strtolower', $criteres));
        foreach ($expanded as $kw) {
            $parts[] = $kw;
        }

        return mb_strtolower(implode(' ', $parts));
    }

    /**
     * Score 0–100 : nombre de tags profil retrouvés dans le texte filière (sous-chaîne).
     */
    private function scoreCompatibiliteLexicale(string $filiereText, array $profileTags): int
    {
        if ($filiereText === '' || $profileTags === []) {
            return 0;
        }

        $hits = 0;
        foreach ($profileTags as $p) {
            $p = mb_strtolower(trim((string) $p));
            if (mb_strlen($p) < 2) {
                continue;
            }
            if (mb_strpos($filiereText, $p) !== false) {
                $hits++;
            }
        }

        return min(100, $hits * 22);
    }

    /**
     * Score académique 0–100 à partir de la moyenne du bac (/20).
     */
    public function scoreAcademique(?float $moyenneSur20): int
    {
        if ($moyenneSur20 === null || $moyenneSur20 <= 0) {
            return self::ACADEMIQUE_NEUTRE;
        }

        return (int) round(min(20.0, max(0.0, $moyenneSur20)) / 20.0 * 100);
    }

    /**
     * Score de compétitivité (proximité au seuil 2025).
     *
     * On compare le score d'orientation de l'étudiant (même échelle que moyennes.score)
     * au seuil officiel (score du dernier orienté).
     *
     * - Plus l'écart absolu est faible, plus le score est élevé.
     * - Léger bonus si l'étudiant est au-dessus du seuil.
     * - Valeur neutre si donnée manquante.
     */
    public function scoreCompetitivite(?float $scoreEtudiant, ?float $seuilMarche): int
    {
        if ($seuilMarche === null || $seuilMarche <= 0) {
            return self::COMPETITIVITE_NEUTRE;
        }

        if ($scoreEtudiant === null || $scoreEtudiant < 0) {
            return self::COMPETITIVITE_NEUTRE;
        }

        $ecart = $scoreEtudiant - $seuilMarche;
        $distance = abs($ecart);

        // 0 point d'écart => 100 ; ~30 points d'écart => 0
        $scoreBase = 100 - (3.33 * $distance);
        $scoreBase = max(0.0, min(100.0, $scoreBase));

        // Au-dessus du seuil => bonus modéré (sans écraser les autres dimensions)
        if ($ecart >= 0) {
            $scoreBase = min(100.0, $scoreBase + 8.0);
        }

        return (int) round($scoreBase);
    }

    public function pickScoreMarcheReference(Filiere $filiere): ?float
    {
        $v = $filiere->score_dernier_oriente_2025 ?? null;
        if ($v !== null && (float) $v > 0) {
            return (float) $v;
        }

        return null;
    }

    /**
     * Heuristique : série du bac (libellé saisi à l’étape moyennes) vs champ type_bac de la filière.
     */
    public function accessibleSelonSerieBac(?string $serieBacLabel, Filiere $filiere): bool
    {
        $typeBac = trim((string) ($filiere->type_bac ?? ''));
        if ($typeBac === '') {
            return true;
        }

        $needles = $this->bacNeedlesFromSpecialite($serieBacLabel);
        if ($needles === []) {
            return true;
        }

        $hay = mb_strtolower($typeBac);

        foreach ($needles as $n) {
            if ($n !== '' && mb_strpos($hay, mb_strtolower($n)) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array{final:int, academique:int, compatibilite:int, competitivite:int, reference:?float, ecart_marche:?float, accessible_bac:bool}
     */
    public function scoresDetailPourFiliere(EtudiantProfile $profile, Filiere $filiere, ?Moyenne $moyenne): array
    {
        $compat = $this->scoreCompatibilite($profile, $filiere);

        $moyenneSur20 = $moyenne ? (float) $moyenne->moyenne : null;
        $acad         = $this->scoreAcademique($moyenneSur20);

        $refMarche = $this->pickScoreMarcheReference($filiere);
        $scoreT    = $moyenne ? (float) $moyenne->score : null;
        $compet    = $this->scoreCompetitivite($scoreT, $refMarche);
        $ecartMarche = ($scoreT !== null && $refMarche !== null) ? abs($scoreT - $refMarche) : null;

        $accessibleBac = $this->accessibleSelonSerieBac($moyenne?->specialite, $filiere);

        $final = (int) round(
            $acad * self::W_ACADEMIQUE
            + $compat * self::W_COMPATIBILITE
            + $compet * self::W_COMPETITIVITE
        );

        if (! $accessibleBac) {
            $final = (int) round($final * 0.9);
        }

        return [
            'final'         => min(100, max(0, $final)),
            'academique'    => $acad,
            'compatibilite' => $compat,
            'competitivite' => $compet,
            'reference'     => $refMarche,
            'ecart_marche'  => $ecartMarche,
            'accessible_bac' => $accessibleBac,
        ];
    }

    public function computeRecommandations(User $etudiant, ?EtudiantProfile $profile = null): void
    {
        $profile ??= EtudiantProfile::where('etudiant_id', $etudiant->id)->first();

        if (! $profile) {
            return;
        }

        $configuredFilieres = Filiere::query()
            ->where(function ($q) {
                $q->whereNotNull('criteres')
                    ->orWhereNotNull('score_dernier_oriente_2025');
            })
            ->get()
            ->filter(function (Filiere $f) {
                $crit = $f->criteres ?? [];
                if (is_array($crit) && $crit !== []) {
                    return true;
                }

                return $this->pickScoreMarcheReference($f) !== null;
            });

        $filieres = $configuredFilieres;
        if ($filieres->isEmpty()) {
            // Fallback: si aucune filière n'est encore configurée côté critères/seuils,
            // on calcule quand même un classement sur les filières existantes.
            $filieres = Filiere::query()
                ->whereNotNull('specialite')
                ->get();
        }

        if ($filieres->isEmpty()) {
            return;
        }

        $moyenne = Moyenne::where('user_id', $etudiant->id)->orderByDesc('created_at')->first();

        $scored = $filieres->map(function (Filiere $f) use ($profile, $moyenne) {
            $d = $this->scoresDetailPourFiliere($profile, $f, $moyenne);

            return [
                'filiere_id'          => $f->id,
                'score'               => $d['final'],
                'score_academique'    => $d['academique'],
                'score_compatibilite' => $d['compatibilite'],
                'score_competitivite' => $d['competitivite'],
                'score_reference'     => $d['reference'],
                'ecart_marche'        => $d['ecart_marche'],
                'accessible_selon_bac' => $d['accessible_bac'],
            ];
        })
            ->sort(function (array $a, array $b) {
                if ($a['score'] !== $b['score']) {
                    return $b['score'] <=> $a['score'];
                }

                $aEcart = $a['ecart_marche'];
                $bEcart = $b['ecart_marche'];
                if ($aEcart === null && $bEcart !== null) {
                    return 1;
                }
                if ($aEcart !== null && $bEcart === null) {
                    return -1;
                }
                if ($aEcart !== null && $bEcart !== null && $aEcart !== $bEcart) {
                    return $aEcart <=> $bEcart;
                }

                if ($a['accessible_selon_bac'] !== $b['accessible_selon_bac']) {
                    return $a['accessible_selon_bac'] ? -1 : 1;
                }

                return $a['filiere_id'] <=> $b['filiere_id'];
            })
            ->take(self::TOP_N)
            ->values();

        Recommandation::where('etudiant_id', $etudiant->id)->delete();

        foreach ($scored as $rang => $item) {
            Recommandation::create([
                'etudiant_id'             => $etudiant->id,
                'filiere_id'              => $item['filiere_id'],
                'score'                   => $item['score'],
                'score_academique'        => $item['score_academique'],
                'score_compatibilite'     => $item['score_compatibilite'],
                'score_competitivite'     => $item['score_competitivite'],
                'score_reference_marche'  => $item['score_reference'],
                'accessible_selon_bac'    => $item['accessible_selon_bac'],
                'rang'                    => $rang + 1,
            ]);
        }
    }

    public function getProfilComplet(User $etudiant): array
    {
        $profile = EtudiantProfile::where('etudiant_id', $etudiant->id)->first();

        // Auto-heal: même sans réponses, on crée un profil minimal pour que
        // le moteur recommande au moins sur les dimensions académiques/marché.
        if (! $profile) {
            $profile = $this->buildProfile($etudiant);
        }

        $moyenne = Moyenne::where('user_id', $etudiant->id)->orderByDesc('created_at')->first();

        $recommandations = Recommandation::with('filiere')
            ->where('etudiant_id', $etudiant->id)
            ->orderBy('rang')
            ->get()
            ->map(fn ($r) => $this->serializeRecommandation($r));

        // Auto-heal: si le profil existe mais pas les recommandations, on recalcule.
        if ($profile && $recommandations->isEmpty()) {
            $this->computeRecommandations($etudiant, $profile);
            $recommandations = Recommandation::with('filiere')
                ->where('etudiant_id', $etudiant->id)
                ->orderBy('rang')
                ->get()
                ->map(fn ($r) => $this->serializeRecommandation($r));
        }

        $scorePlus7 = $moyenne ? (float) $moyenne->score_plus_7 : null;
        $scoreBase  = $moyenne ? (float) $moyenne->score : null;
        $recommandations = $recommandations->map(
            fn (array $r) => $this->appendSevenBonus($r, $scoreBase, $scorePlus7)
        );

        $etatRecommandation = $this->detectRecommendationState(
            $profile,
            $recommandations->count()
        );

        return [
            'profil' => $profile ? [
                'interets'    => $profile->interets    ?? [],
                'competences' => $profile->competences ?? [],
                'preferences' => $profile->preferences ?? [],
            ] : null,
            'etat_recommandation' => $etatRecommandation,
            'recommandations' => $recommandations,
            'contexte_academique' => [
                'a_moyenne'              => $moyenne !== null,
                'moyenne_generale'       => $moyenne ? (float) $moyenne->moyenne : null,
                'score_orientation_T'    => $moyenne ? (float) $moyenne->score : null,
                'score_orientation_T_plus_7' => $moyenne ? (float) $moyenne->score_plus_7 : null,
                'serie_bac_label'        => $moyenne?->specialite,
                'note_sur_marche'        => 'Importer les seuils (dernier orienté) dans les filières au même format que le score calculé ici (ex. moyenne × 2, formule T simplifiée de l’app).',
                'poids'                  => [
                    'academique'    => self::W_ACADEMIQUE,
                    'compatibilite' => self::W_COMPATIBILITE,
                    'competitivite' => self::W_COMPETITIVITE,
                ],
            ],
        ];
    }

    private function detectRecommendationState(?EtudiantProfile $profile, int $recoCount): string
    {
        if (! $profile) {
            return 'profil_incomplet';
        }

        if ($recoCount > 0) {
            return 'ok';
        }

        $allFilieresCount = Filiere::query()->count();
        if ($allFilieresCount === 0) {
            return 'aucune_filiere';
        }

        $configuredCount = Filiere::query()
            ->where(function ($q) {
                $q->whereNotNull('criteres')
                    ->orWhereNotNull('score_dernier_oriente_2025');
            })
            ->count();

        if ($configuredCount === 0) {
            return 'aucune_filiere_configuree';
        }

        return 'aucune_recommandation';
    }

    private function serializeRecommandation(Recommandation $r): array
    {
        $f = $r->filiere;

        return [
            'rang'    => $r->rang,
            'score'   => $r->score,
            'scores_detail' => [
                'academique'    => $r->score_academique,
                'compatibilite' => $r->score_compatibilite,
                'competitivite' => $r->score_competitivite,
            ],
            'score_reference_marche' => $r->score_reference_marche !== null ? (float) $r->score_reference_marche : null,
            'accessible_selon_bac'   => $r->accessible_selon_bac,
            'filiere' => [
                'id'          => $f->id,
                'nom'         => $f->specialite ?? $f->nom ?? '',
                'licence'     => $f->licence ?? '',
                'universite'  => $f->universite ?? '',
                'type_bac'    => $f->type_bac ?? '',
                'formule'     => $f->formule ?? '',
                'capacite'    => $f->capacite,
                'scores_marche' => [
                    '2025' => $f->score_dernier_oriente_2025 !== null ? (float) $f->score_dernier_oriente_2025 : null,
                ],
            ],
        ];
    }

    private function appendSevenBonus(array $reco, ?float $scoreBase, ?float $scorePlus7): array
    {
        $reference = $reco['score_reference_marche'] ?? null;
        if ($reference === null || $scoreBase === null || $scorePlus7 === null) {
            $reco['bonus_7'] = [
                'applicable' => false,
                'proche' => false,
                'recommande_avec_bonus' => false,
            ];
            return $reco;
        }

        $diff = $reference - $scoreBase;
        $reco['bonus_7'] = [
            'applicable' => true,
            'proche' => $diff > 0 && $diff <= 7,
            'recommande_avec_bonus' => $scoreBase < $reference && $scorePlus7 >= $reference,
            'score_reference' => $reference,
            'score_base' => $scoreBase,
            'score_plus_7' => $scorePlus7,
            'ecart' => round($diff, 2),
        ];

        return $reco;
    }

    private function bacNeedlesFromSpecialite(?string $specialite): array
    {
        if ($specialite === null || trim($specialite) === '') {
            return [];
        }

        $s = mb_strtolower(trim($specialite));

        $map = [
            'sciences expérimentales' => ['expériment', 'تجريب', 'science exp', 'svt'],
            'mathématiques' => ['math', 'رياض'],
            'sport' => ['sport', 'رياض', 'physique'],
            'lettres' => ['lettre', 'ادب', 'آداب', 'adab'],
            'économie et gestion' => ['économie', 'gestion', 'اقتصاد', 'تصرف'],
            'sciences techniques' => ['technique', 'تقنية', 'technical'],
            'informatique' => ['informatique', 'info', 'اعلام', 'إعلام'],
        ];

        foreach ($map as $label => $needles) {
            if (mb_strpos($s, mb_strtolower($label)) !== false) {
                return array_merge([$label], $needles);
            }
        }

        return [mb_substr($s, 0, 5)];
    }

    private function extractQuestionTags(object $question): array
    {
        $raw = $question->mots_cles ?? null;

        if (is_array($raw)) {
            return array_values(array_filter(array_map(
                fn ($t) => mb_strtolower(trim((string) $t)),
                $raw
            )));
        }

        if (is_string($raw) && trim($raw) !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return array_values(array_filter(array_map(
                    fn ($t) => mb_strtolower(trim((string) $t)),
                    $decoded
                )));
            }

            $parts = preg_split('/[,;|]/', $raw) ?: [];
            return array_values(array_filter(array_map(
                fn ($t) => mb_strtolower(trim((string) $t)),
                $parts
            )));
        }

        return [];
    }

    private function reponseEstPositive(string $reponse): bool
    {
        $r = mb_strtolower(trim($reponse));

        if (mb_strlen($r) < 2) {
            return false;
        }

        $negatifs = ['non', 'jamais', 'pas du tout', 'nul', 'aucun'];
        foreach ($negatifs as $mot) {
            if ($r === $mot) {
                return false;
            }
        }

        return true;
    }

    public function genererMessageAuto(User $etudiant): string
    {
        $profil = EtudiantProfile::where('etudiant_id', $etudiant->id)->first();
        $recommandations = Recommandation::with('filiere')
            ->where('etudiant_id', $etudiant->id)
            ->orderBy('rang')
            ->take(3)
            ->get();

        if (! $profil && $recommandations->isEmpty()) {
            return "Bonjour 👋 Cet étudiant n'a pas encore rempli le questionnaire.";
        }

        $msg = "Bonjour 👋 Voici un résumé pour démarrer la discussion :\n\n";
        $msg .= "Les scores détaillés (académique, compatibilité, compétitivité, seuils ministère) sont dans l’onglet « Recommandations ».\n\n";

        foreach ($recommandations as $i => $r) {
            $emoji = match ($i) {
                0 => '🥇',
                1 => '🥈',
                2 => '🥉',
                default => '•'
            };
            $nom = $r->filiere->specialite ?? $r->filiere->nom ?? 'Filière';
            $msg .= "{$emoji} {$nom} — score global {$r->score} %\n";
        }

        $msg .= "\n";

        if ($recommandations->isNotEmpty()) {
            $top   = $recommandations->first();
            $score = $top->score;
            $nom   = $top->filiere->specialite ?? $top->filiere->nom ?? '';

            if ($score >= 80) {
                $msg .= "✅ {$nom} ressort en tête du classement intelligent.";
            } elseif ($score >= 60) {
                $msg .= "👍 {$nom} est une piste solide à creuser ensemble.";
            } else {
                $msg .= "💬 Les pistes sont à affiner — échangeons pour préciser le projet.";
            }

            $moyenne = Moyenne::where('user_id', $etudiant->id)->orderByDesc('created_at')->first();
            $reference = $top->score_reference_marche !== null ? (float) $top->score_reference_marche : null;
            if ($moyenne && $reference !== null) {
                $scoreBase = (float) $moyenne->score;
                $scorePlus7 = (float) $moyenne->score_plus_7;
                if ($scoreBase < $reference && $scorePlus7 >= $reference) {
                    $msg .= "\n⭐ Cette filière devient très recommandée avec la bonification de +7%.";
                }
            }
        }

        if ($profil && ! empty($profil->interets)) {
            $interets = is_array($profil->interets)
                ? array_slice($profil->interets, 0, 3)
                : array_slice(json_decode($profil->interets, true) ?? [], 0, 3);
            $msg .= "\n\n🎯 Intérêts détectés : ".implode(', ', $interets);
        }

        $msg .= "\n\nN'hésitez pas à me poser vos questions 😊";

        return $msg;
    }
}
