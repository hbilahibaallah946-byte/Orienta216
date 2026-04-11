<?php
namespace Database\Seeders;

use App\Models\Question;
use Illuminate\Database\Seeder;

class QuestionMotsClesSeeder extends Seeder
{
    public function run(): void
    {
        // ── Si aucune question n'existe, on en crée un jeu complet ──────────
        if (Question::count() === 0) {
            $this->createDefaultQuestions();
        } else {
            // Sinon on met à jour les existantes avec des mots-clés par défaut
            $this->updateExistingQuestions();
        }
    }

    private function createDefaultQuestions(): void
    {
        $questions = [
            // ── Intérêts ──────────────────────────────────────────────────────
            [
                'question' => 'Quels domaines t\'intéressent le plus ?',
                'categorie' => 'interet',
                'mots_cles' => ['curiosité', 'exploration'],
                'is_active' => true,
            ],
            [
                'question' => 'Aimes-tu les mathématiques et la logique ?',
                'categorie' => 'interet',
                'mots_cles' => ['maths', 'logique', 'rigueur'],
                'is_active' => true,
            ],
            [
                'question' => 'Es-tu passionné(e) par la technologie et l\'informatique ?',
                'categorie' => 'interet',
                'mots_cles' => ['technologie', 'programmation', 'informatique'],
                'is_active' => true,
            ],
            [
                'question' => 'T\'intéresses-tu aux sciences naturelles (biologie, chimie, physique) ?',
                'categorie' => 'interet',
                'mots_cles' => ['sciences', 'biologie', 'chimie', 'expérimentation'],
                'is_active' => true,
            ],
            [
                'question' => 'Aimes-tu les langues et la communication ?',
                'categorie' => 'interet',
                'mots_cles' => ['communication', 'langues', 'écriture', 'cultures'],
                'is_active' => true,
            ],
            [
                'question' => 'Es-tu attiré(e) par les arts, le design ou la créativité ?',
                'categorie' => 'interet',
                'mots_cles' => ['créativité', 'design', 'esthétique', 'expression'],
                'is_active' => true,
            ],
            [
                'question' => 'T\'intéresses-tu à l\'économie et aux affaires ?',
                'categorie' => 'interet',
                'mots_cles' => ['économie', 'organisation', 'leadership'],
                'is_active' => true,
            ],
            [
                'question' => 'Aimes-tu les relations humaines et aider les autres ?',
                'categorie' => 'interet',
                'mots_cles' => ['empathie', 'humain', 'communication'],
                'is_active' => true,
            ],

            // ── Compétences ───────────────────────────────────────────────────
            [
                'question' => 'Te considères-tu bon(ne) en résolution de problèmes complexes ?',
                'categorie' => 'competence',
                'mots_cles' => ['résolution_problèmes', 'logique', 'analyse'],
                'is_active' => true,
            ],
            [
                'question' => 'As-tu de bonnes capacités d\'analyse et de raisonnement ?',
                'categorie' => 'competence',
                'mots_cles' => ['analyse', 'logique', 'abstraction'],
                'is_active' => true,
            ],
            [
                'question' => 'As-tu déjà programmé ou utilisé des outils numériques avancés ?',
                'categorie' => 'competence',
                'mots_cles' => ['programmation', 'technologie', 'informatique'],
                'is_active' => true,
            ],
            [
                'question' => 'Es-tu à l\'aise avec les chiffres et les calculs ?',
                'categorie' => 'competence',
                'mots_cles' => ['maths', 'statistiques', 'rigueur'],
                'is_active' => true,
            ],
            [
                'question' => 'As-tu de bonnes capacités de communication orale et écrite ?',
                'categorie' => 'competence',
                'mots_cles' => ['communication', 'écriture', 'argumentation'],
                'is_active' => true,
            ],
            [
                'question' => 'Es-tu capable de travailler de manière autonome et organisée ?',
                'categorie' => 'competence',
                'mots_cles' => ['autonomie', 'organisation', 'rigueur'],
                'is_active' => true,
            ],

            // ── Préférences ───────────────────────────────────────────────────
            [
                'question' => 'Préfères-tu les activités pratiques ou théoriques ?',
                'categorie' => 'preference',
                'mots_cles' => ['pratique', 'expérimentation'],
                'is_active' => true,
            ],
            [
                'question' => 'Aimes-tu travailler en équipe ?',
                'categorie' => 'preference',
                'mots_cles' => ['travail_equipe', 'communication', 'organisation'],
                'is_active' => true,
            ],
            [
                'question' => 'Préfères-tu un travail en bureau ou sur le terrain ?',
                'categorie' => 'preference',
                'mots_cles' => ['organisation', 'pratique'],
                'is_active' => true,
            ],
            [
                'question' => 'Souhaites-tu un métier avec impact social direct ?',
                'categorie' => 'preference',
                'mots_cles' => ['humain', 'empathie', 'communication'],
                'is_active' => true,
            ],
            [
                'question' => 'Es-tu attiré(e) par les métiers d\'innovation et de recherche ?',
                'categorie' => 'preference',
                'mots_cles' => ['curiosité', 'analyse', 'autonomie', 'résolution_problèmes'],
                'is_active' => true,
            ],
        ];

        foreach ($questions as $q) {
            Question::create($q);
        }

        $this->command->info('✅ ' . count($questions) . ' questions créées avec mots-clés.');
    }

    private function updateExistingQuestions(): void
    {
        // Règles d'association automatique basées sur des mots-clés dans le texte
        $rules = [
            ['pattern' => '/mathématique|calcul|chiffre|algèbre/i',  'cat' => 'interet',    'tags' => ['maths', 'logique', 'rigueur']],
            ['pattern' => '/technologie|informatique|numérique/i',   'cat' => 'interet',    'tags' => ['technologie', 'programmation']],
            ['pattern' => '/science|biologie|chimie|physique/i',     'cat' => 'interet',    'tags' => ['sciences', 'expérimentation']],
            ['pattern' => '/communication|langue|écrire/i',          'cat' => 'interet',    'tags' => ['communication', 'langues']],
            ['pattern' => '/créatif|design|art|esthétique/i',        'cat' => 'interet',    'tags' => ['créativité', 'design']],
            ['pattern' => '/économie|commerce|gestion|finance/i',    'cat' => 'interet',    'tags' => ['économie', 'organisation']],
            ['pattern' => '/programmer|coder|développer/i',          'cat' => 'competence', 'tags' => ['programmation', 'logique']],
            ['pattern' => '/analyser|résoudre|logique/i',            'cat' => 'competence', 'tags' => ['analyse', 'résolution_problèmes']],
            ['pattern' => '/team|équipe|groupe/i',                   'cat' => 'preference', 'tags' => ['travail_equipe']],
            ['pattern' => '/pratique|terrain|concret/i',             'cat' => 'preference', 'tags' => ['pratique']],
            ['pattern' => '/humain|aider|social|relation/i',         'cat' => 'preference', 'tags' => ['humain', 'empathie']],
        ];

        $updated = 0;
        foreach (Question::all() as $question) {
            $tags = [];
            $cat  = 'interet';
            foreach ($rules as $rule) {
                if (preg_match($rule['pattern'], $question->question)) {
                    $tags = array_values(array_unique(array_merge($tags, $rule['tags'])));
                    $cat  = $rule['cat'];
                }
            }
            if (!empty($tags)) {
                $question->update(['mots_cles' => $tags, 'categorie' => $cat]);
                $updated++;
            }
        }
        $this->command->info("✅ {$updated} questions mises à jour avec des mots-clés.");
    }
}