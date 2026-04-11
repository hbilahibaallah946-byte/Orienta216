<?php
// database/seeders/FiliereCriteresSeeder.php
//
// Lance avec : php artisan db:seed --class=FiliereCriteresSeeder
//
// Ce seeder ajoute des critères (mots-clés de matching) à vos filières.
// Adaptez les noms de filières à votre base de données réelle.

namespace Database\Seeders;

use App\Models\Filiere;
use Illuminate\Database\Seeder;

class FiliereCriteresSeeder extends Seeder
{
    /**
     * Correspondances entre mots trouvés dans le nom/spécialité d'une filière
     * et les critères de matching.
     */
    private array $mapping = [
        // Informatique / Numérique
        'informatique'  => ['logique', 'maths', 'technologie', 'programmation', 'résolution_problèmes', 'autonomie'],
        'réseau'        => ['technologie', 'logique', 'résolution_problèmes', 'travail_equipe', 'infrastructure'],
        'génie logiciel'=> ['programmation', 'logique', 'maths', 'créativité', 'autonomie'],
        'data'          => ['maths', 'statistiques', 'logique', 'analyse', 'programmation'],
        'intelligence'  => ['maths', 'logique', 'programmation', 'analyse', 'curiosité'],
        'cybersécurité' => ['logique', 'technologie', 'curiosité', 'résolution_problèmes'],
        'multimédia'    => ['créativité', 'technologie', 'design', 'communication'],

        // Sciences
        'mathématiques' => ['maths', 'logique', 'abstraction', 'rigueur', 'autonomie'],
        'physique'      => ['maths', 'sciences', 'expérimentation', 'rigueur', 'curiosité'],
        'chimie'        => ['sciences', 'expérimentation', 'rigueur', 'curiosité', 'laboratoire'],
        'biologie'      => ['sciences', 'nature', 'expérimentation', 'mémoire', 'curiosité'],
        'médecine'      => ['sciences', 'biologie', 'mémoire', 'empathie', 'rigueur', 'humain'],
        'pharmacie'     => ['sciences', 'chimie', 'mémoire', 'rigueur', 'humain'],

        // Gestion / Économie
        'gestion'       => ['organisation', 'communication', 'leadership', 'économie', 'travail_equipe'],
        'économie'      => ['maths', 'analyse', 'économie', 'communication', 'curiosité'],
        'finance'       => ['maths', 'analyse', 'économie', 'rigueur', 'organisation'],
        'comptabilité'  => ['maths', 'organisation', 'rigueur', 'économie', 'détail'],
        'commerce'      => ['communication', 'leadership', 'économie', 'créativité', 'travail_equipe'],
        'marketing'     => ['créativité', 'communication', 'économie', 'design', 'analyse'],

        // Droit / Sciences humaines
        'droit'         => ['communication', 'mémoire', 'rigueur', 'analyse', 'argumentation'],
        'sociologie'    => ['communication', 'humain', 'curiosité', 'analyse', 'écriture'],
        'psychologie'   => ['empathie', 'humain', 'communication', 'curiosité', 'analyse'],
        'histoire'      => ['mémoire', 'écriture', 'curiosité', 'analyse', 'humain'],
        'lettres'       => ['écriture', 'communication', 'créativité', 'analyse', 'lecture'],
        'langues'       => ['communication', 'écriture', 'curiosité', 'mémoire', 'cultures'],

        // Ingénierie / Technique
        'génie civil'   => ['maths', 'physique', 'organisation', 'rigueur', 'pratique'],
        'génie mécanique'=> ['maths', 'physique', 'pratique', 'résolution_problèmes', 'technologie'],
        'génie électrique'=> ['maths', 'physique', 'technologie', 'pratique', 'logique'],
        'architecture'  => ['créativité', 'maths', 'design', 'rigueur', 'pratique'],

        // Médical / Paramédical
        'infirmier'     => ['empathie', 'humain', 'sciences', 'pratique', 'organisation'],
        'kinésithérapie'=> ['empathie', 'pratique', 'sciences', 'humain', 'organisation'],

        // Agriculture / Environnement
        'agriculture'   => ['nature', 'sciences', 'pratique', 'organisation', 'environnement'],
        'environnement' => ['nature', 'sciences', 'curiosité', 'analyse', 'environnement'],

        // Arts / Design
        'arts'          => ['créativité', 'design', 'expression', 'pratique', 'esthétique'],
        'design'        => ['créativité', 'design', 'technologie', 'expression', 'esthétique'],

        // Éducation
        'éducation'     => ['communication', 'empathie', 'humain', 'organisation', 'patience'],
        'enseignement'  => ['communication', 'empathie', 'humain', 'organisation', 'patience'],
    ];

     public function run(): void
    {
        $filieres = Filiere::all();
        $updated = 0;
        
        foreach ($filieres as $filiere) {
            $nom = strtolower($filiere->specialite ?? $filiere->nom ?? '');
            
            $criteres = [];
            foreach ($this->mapping as $keyword => $tags) {
                if (str_contains($nom, $keyword)) {
                    $criteres = array_values(array_unique(array_merge($criteres, $tags)));
                }
            }
            
            if (empty($criteres)) {
                $criteres = ['organisation', 'communication', 'curiosité', 'travail_equipe'];
            }
            
            $filiere->update(['criteres' => $criteres]);
            $updated++;
        }
        
        $this->command->info("✅ {$updated} filières mises à jour avec des critères.");
    }
}