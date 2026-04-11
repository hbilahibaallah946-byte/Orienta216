<?php
// app/Services/ProfilService.php

namespace App\Services;

use App\Models\EtudiantProfile;
use App\Models\Filiere;
use App\Models\Recommandation;
use App\Models\User;

class ProfilService
{
    // ══════════════════════════════════════════════════════════════════════════
    // 1. CONSTRUIRE LE PROFIL DEPUIS LE QUESTIONNAIRE
    // ══════════════════════════════════════════════════════════════════════════

    public function buildProfile(User $etudiant): EtudiantProfile
    {
        // Charge les réponses via la relation (qui utilise maintenant etudiant_id)
        $reponses = $etudiant->reponses()->with('question')->get();

        $interets    = [];
        $competences = [];
        $preferences = [];

        foreach ($reponses as $reponse) {
            $question = $reponse->question;

            // Ignorer si question supprimée ou sans mots-clés
            if (!$question || empty($question->mots_cles)) {
                continue;
            }

            if ($this->reponseEstPositive($reponse->reponse)) {
                // mots_cles est déjà un array grâce au cast du modèle Question
                $tags = array_map('strtolower', (array) $question->mots_cles);

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

    // ══════════════════════════════════════════════════════════════════════════
    // 2. SCORE PROFIL vs FILIÈRE
    // ══════════════════════════════════════════════════════════════════════════

    public function scoreFiliere(EtudiantProfile $profile, Filiere $filiere): int
    {
        $criteres = array_map('strtolower', (array) ($filiere->criteres ?? []));

        if (empty($criteres)) {
            return 0;
        }

        $profileTags = $profile->allTags();
        $matches     = count(array_intersect($criteres, $profileTags));
        $score       = (int) round(($matches / count($criteres)) * 100);

        // Bonus compétences
        $competenceTags    = array_map('strtolower', (array) ($profile->competences ?? []));
        $competenceMatches = count(array_intersect($criteres, $competenceTags));
        if ($competenceMatches > 0) {
            $score = min(100, $score + (int) round(($competenceMatches / count($criteres)) * 15));
        }

        return $score;
    }

    // ══════════════════════════════════════════════════════════════════════════
    // 3. TOP-3 RECOMMANDATIONS
    // ══════════════════════════════════════════════════════════════════════════

    public function computeRecommandations(User $etudiant, ?EtudiantProfile $profile = null): void
    {
        $profile ??= EtudiantProfile::where('etudiant_id', $etudiant->id)->first();

        if (!$profile) {
            return;
        }

        $filieres = Filiere::whereNotNull('criteres')->get();

        if ($filieres->isEmpty()) {
            return;
        }

        $scores = $filieres
            ->map(fn(Filiere $f) => [
                'filiere_id' => $f->id,
                'score'      => $this->scoreFiliere($profile, $f),
            ])
            ->sortByDesc('score')
            ->take(3)
            ->values();

        Recommandation::where('etudiant_id', $etudiant->id)->delete();

        foreach ($scores as $rang => $item) {
            Recommandation::create([
                'etudiant_id' => $etudiant->id,
                'filiere_id'  => $item['filiere_id'],
                'score'       => $item['score'],
                'rang'        => $rang + 1,
            ]);
        }
    }

    // ══════════════════════════════════════════════════════════════════════════
    // 4. PROFIL COMPLET (API)
    // ══════════════════════════════════════════════════════════════════════════

    public function getProfilComplet(User $etudiant): array
    {
        $profile = EtudiantProfile::where('etudiant_id', $etudiant->id)->first();

        $recommandations = Recommandation::with('filiere')
            ->where('etudiant_id', $etudiant->id)
            ->orderBy('rang')
            ->get()
            ->map(fn($r) => [
                'rang'    => $r->rang,
                'score'   => $r->score,
                'filiere' => [
                    'id'         => $r->filiere->id,
                    'nom'        => $r->filiere->specialite ?? $r->filiere->nom ?? '',
                    'universite' => $r->filiere->universite ?? '',
                    'type_bac'   => $r->filiere->type_bac   ?? '',
                ],
            ]);

        return [
            'profil' => $profile ? [
                'interets'    => $profile->interets    ?? [],
                'competences' => $profile->competences ?? [],
                'preferences' => $profile->preferences ?? [],
            ] : null,
            'recommandations' => $recommandations,
        ];
    }

    // ══════════════════════════════════════════════════════════════════════════
    // HELPER PRIVÉ
    // ══════════════════════════════════════════════════════════════════════════

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
    
    /**
     * Génère un message automatique pour le conseiller
     * basé sur le profil et les recommandations de l'étudiant
     */
    public function genererMessageAuto(User $etudiant): string
    {
        $profil = EtudiantProfile::where('etudiant_id', $etudiant->id)->first();
        $recommandations = Recommandation::with('filiere')
            ->where('etudiant_id', $etudiant->id)
            ->orderBy('score', 'desc')
            ->take(3)
            ->get();

        if (!$profil && $recommandations->isEmpty()) {
            return "Bonjour 👋 Cet étudiant n'a pas encore rempli le questionnaire.";
        }

        $msg = "Bonjour 👋 Selon le questionnaire, voici mes suggestions :\n\n";
        
        foreach ($recommandations as $i => $r) {
            $emoji = match ($i) {
                0 => '🥇',
                1 => '🥈',
                2 => '🥉',
                default => '•'
            };
            $msg .= "{$emoji} {$r->filiere->specialite} ({$r->score}%)\n";
        }
        
        $msg .= "\n";
        
        if ($recommandations->isNotEmpty()) {
            $top = $recommandations->first();
            $score = $top->score;
            $nom = $top->filiere->specialite;
            
            if ($score >= 80) {
                $msg .= "✅ {$nom} correspond très bien à ton profil !";
            } elseif ($score >= 60) {
                $msg .= "👍 {$nom} est une bonne option, à explorer ensemble.";
            } else {
                $msg .= "💬 Les résultats sont mitigés — parlons-en ensemble.";
            }
        }
        
        if ($profil && !empty($profil->interets)) {
            $interets = is_array($profil->interets) 
                ? array_slice($profil->interets, 0, 3) 
                : array_slice(json_decode($profil->interets, true) ?? [], 0, 3);
            $msg .= "\n\n🎯 Intérêts détectés : " . implode(', ', $interets);
        }
        
        $msg .= "\n\nN'hésitez pas à me poser vos questions 😊";
        
        return $msg;
    }
}