<?php

namespace App\Console\Commands;

use App\Models\Filiere;
use Illuminate\Console\Command;

/**
 * Importe capacité et scores « dernier orienté » depuis un CSV nettoyé
 * (après extraction PDF orientation.tn via Tabula / tableur).
 *
 * Colonnes attendues (en-tête, séparateur ;) ou (, détecté automatiquement) :
 *   code, capacite, score_2025, score_2024, score_2023
 * Au moins un de code ou specialite doit être présent pour rattacher la ligne.
 */
class ImportFiliereOrientationScores extends Command
{
    protected $signature = 'filieres:import-orientation-scores
                            {path : Chemin du fichier CSV}
                            {--dry-run : Affiche les mises à jour sans écrire en base}';

    protected $description = 'Importe capacite et score_dernier_oriente_20xx pour les filières (CSV nettoyé depuis les guides ministère)';

    public function handle(): int
    {
        $path = $this->argument('path');
        if (! is_readable($path)) {
            $this->error("Fichier illisible : {$path}");

            return self::FAILURE;
        }

        $raw = file_get_contents($path);
        if ($raw === false) {
            $this->error('Lecture impossible.');

            return self::FAILURE;
        }

        $delimiter = str_contains($raw, ';') ? ';' : ',';
        $fh        = fopen($path, 'r');
        if ($fh === false) {
            return self::FAILURE;
        }

        $header = fgetcsv($fh, 0, $delimiter);
        if ($header === false) {
            fclose($fh);
            $this->error('CSV vide.');

            return self::FAILURE;
        }

        $header = array_map(fn ($h) => strtolower(trim((string) $h)), $header);
        $idx    = array_flip($header);

        $requiredAny = ['code', 'specialite'];
        if (! isset($idx['code']) && ! isset($idx['specialite'])) {
            fclose($fh);
            $this->error('Le CSV doit contenir une colonne "code" et/ou "specialite".');

            return self::FAILURE;
        }

        $dry = (bool) $this->option('dry-run');
        $n   = 0;

        while (($row = fgetcsv($fh, 0, $delimiter)) !== false) {
            $code       = isset($idx['code']) ? trim((string) ($row[$idx['code']] ?? '')) : '';
            $specialite = isset($idx['specialite']) ? trim((string) ($row[$idx['specialite']] ?? '')) : '';

            $filiere = null;
            if ($code !== '') {
                $filiere = Filiere::where('code', $code)->first();
            }
            if (! $filiere && $specialite !== '') {
                $filiere = Filiere::where('specialite', $specialite)->first();
            }

            if (! $filiere) {
                $this->warn("Ligne ignorée (aucune filière) : code={$code} specialite={$specialite}");

                continue;
            }

            $data = [];
            if (isset($idx['capacite']) && ($row[$idx['capacite']] ?? '') !== '') {
                $data['capacite'] = (int) preg_replace('/\D/', '', (string) $row[$idx['capacite']]);
            }
            foreach (['2025' => 'score_dernier_oriente_2025'] as $year => $col) {
                $key = "score_{$year}";
                if (isset($idx[$key]) && ($row[$idx[$key]] ?? '') !== '') {
                    $v = str_replace(',', '.', trim((string) $row[$idx[$key]]));
                    if (is_numeric($v)) {
                        $data[$col] = $v;
                    }
                }
            }

            if ($data === []) {
                continue;
            }

            $this->line("[{$filiere->id}] {$filiere->specialite} → ".json_encode($data, JSON_UNESCAPED_UNICODE));
            if (! $dry) {
                $filiere->update($data);
            }
            $n++;
        }

        fclose($fh);

        $this->info($dry ? "Dry-run : {$n} ligne(s) traitées (aucune écriture)." : "{$n} filière(s) mise(s) à jour.");

        return self::SUCCESS;
    }
}
