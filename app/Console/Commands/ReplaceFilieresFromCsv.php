<?php

namespace App\Console\Commands;

use App\Models\Filiere;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReplaceFilieresFromCsv extends Command
{
    protected $signature = 'filieres:replace-from-csv
                            {path : Chemin du fichier CSV}
                            {--dry-run : Analyse sans écriture}';

    protected $description = 'Remplace toutes les filières à partir d’un CSV nettoyé (mapping colonnes FR -> DB)';

    public function handle(): int
    {
        $path = (string) $this->argument('path');
        if (! is_readable($path)) {
            $this->error("CSV introuvable/illisible: {$path}");
            return self::FAILURE;
        }

        $delimiter = $this->guessDelimiter($path);
        $fh = fopen($path, 'r');
        if (! $fh) {
            $this->error("Impossible d'ouvrir le CSV.");
            return self::FAILURE;
        }

        $header = fgetcsv($fh, 0, $delimiter);
        if (! $header) {
            fclose($fh);
            $this->error('CSV vide.');
            return self::FAILURE;
        }

        $map = $this->buildHeaderMap($header);
        if (! isset($map['code'])) {
            fclose($fh);
            $this->error("Colonne Code obligatoire.");
            return self::FAILURE;
        }

        $rows = [];
        while (($r = fgetcsv($fh, 0, $delimiter)) !== false) {
            if ($this->rowIsEmpty($r)) {
                continue;
            }
            $row = $this->mapRow($r, $map);
            if (! $row || empty($row['code'])) {
                continue;
            }
            $rows[$row['code']] = $row; // dédoublonnage par code
        }
        fclose($fh);

        $rows = array_values($rows);
        if ($rows === []) {
            $this->error('Aucune ligne exploitable.');
            return self::FAILURE;
        }

        $this->info('Lignes exploitables: '.count($rows));
        if ($this->option('dry-run')) {
            foreach (array_slice($rows, 0, 15) as $x) {
                $this->line("{$x['code']} | {$x['licence']} | {$x['specialite']}");
            }
            return self::SUCCESS;
        }

        DB::transaction(function () use ($rows) {
            Filiere::query()->delete();
            foreach ($rows as $row) {
                Filiere::create($row);
            }
        });

        $this->info('Remplacement terminé: '.count($rows).' filières importées.');
        return self::SUCCESS;
    }

    private function guessDelimiter(string $path): string
    {
        $sample = file_get_contents($path, false, null, 0, 4096) ?: '';
        return substr_count($sample, ';') > substr_count($sample, ',') ? ';' : ',';
    }

    private function normalizeHeader(string $h): string
    {
        $h = mb_strtolower(trim($h));
        $h = str_replace(['(', ')', "\u2018", "'", '"', "\u00e9", "\u00e8", "\u00ea", "\u00e0", "\u00fb", "\u00ee", "\u00ef", "\u00f4", "\u00f9", '/', '-'], [' ', ' ', ' ', ' ', ' ', 'e', 'e', 'e', 'a', 'u', 'i', 'i', 'o', 'u', ' ', ' '], $h);
        $h = preg_replace('/\s+/', ' ', $h) ?? $h;
        return trim($h);
    }

    /**
     * Normalize Arabic text (presentation forms -> standard Unicode).
     */
    private function normalizeArabic(string $v): string
    {
        if (class_exists(\Normalizer::class)) {
            $n = \Normalizer::normalize($v, \Normalizer::FORM_KC);
            if (is_string($n) && $n !== '') {
                $v = $n;
            }
        }
        return $v;
    }

    private function buildHeaderMap(array $header): array
    {
        $m = [];
        foreach ($header as $i => $hRaw) {
            $hFr = $this->normalizeHeader((string) $hRaw);
            // Also normalize Arabic presentation forms for Arabic column headers
            $hAr = $this->normalizeArabic(mb_strtolower(trim((string) $hRaw)));

            // French header matching
            if (str_contains($hFr, 'filiere') || str_contains($hFr, 'licence')) $m['licence'] = $i;
            if ($hFr === 'code' || str_contains($hFr, 'code')) $m['code'] = $i;
            if (str_contains($hFr, 'etablissement') || str_contains($hFr, 'universite')) $m['universite'] = $i;
            if (str_contains($hFr, 'parcours') || str_contains($hFr, 'specialite')) $m['specialite'] = $i;
            if (str_contains($hFr, 'serie') || str_contains($hFr, 'type_bac') || str_contains($hFr, 'type bac')) $m['type_bac'] = $i;
            if (str_contains($hFr, 'formule du score')) $m['formule_score'] = $i;
            if (str_contains($hFr, 'conditions') || str_contains($hFr, 'formule')) $m['conditions'] = $i;
            if (str_contains($hFr, 'bandeau') || str_contains($hFr, 'critere')) $m['criteres'] = $i;
            if (str_contains($hFr, 'score dernier oriente') || str_contains($hFr, 'moyenne dernier') || str_contains($hFr, 'moujeh') || str_contains($hFr, '2024')) $m['score_2025'] = $i;
            if (str_contains($hFr, 'capacite') || str_contains($hFr, 'energie') || str_contains($hFr, 'places')) $m['capacite'] = $i;

            // Arabic header matching (مجموع اخر موجه 2024 = total last oriented 2024)
            if (mb_strpos($hAr, "\u0645\u062C\u0645\u0648\u0639") !== false || mb_strpos($hAr, "\u0645\u0648\u062C\u0647") !== false) {
                $m['score_2025'] = $i;
            }
            // نوع الباكالوريا = bac type
            if (mb_strpos($hAr, "\u0628\u0627\u0643\u0627\u0644\u0648\u0631\u064A\u0627") !== false || mb_strpos($hAr, "\u0646\u0648\u0639") !== false && mb_strpos($hAr, "\u0628\u0627\u0643") !== false) {
                $m['type_bac'] = $i;
            }
            // المؤسسة = institution
            if (mb_strpos($hAr, "\u0645\u0624\u0633\u0633") !== false || mb_strpos($hAr, "\u062C\u0627\u0645\u0639") !== false) {
                $m['universite'] = $i;
            }
            // الإجازة / الشعبة = licence / branch
            if (mb_strpos($hAr, "\u0625\u062C\u0627\u0632") !== false || mb_strpos($hAr, "\u0634\u0639\u0628") !== false) {
                $m['licence'] = $i;
            }
            // الرمز = code
            if (mb_strpos($hAr, "\u0631\u0645\u0632") !== false) {
                $m['code'] = $i;
            }
            // الطاقة = capacity
            if (mb_strpos($hAr, "\u0637\u0627\u0642") !== false) {
                $m['capacite'] = $i;
            }
        }
        return $m;
    }

    private function mapRow(array $r, array $m): ?array
    {
        $get = fn (string $k) => isset($m[$k]) ? trim((string) ($r[$m[$k]] ?? '')) : '';
        $code = $get('code');
        if ($code === '') return null;

        $licence = $get('licence');
        $specialite = $get('specialite');
        $universite = $get('universite');
        $typeBac = $get('type_bac');
        $fScore = $get('formule_score');
        $fCond = $get('conditions');
        $formule = trim(implode(' | ', array_values(array_filter([$fScore, $fCond], fn ($x) => $x !== ''))));
        $criteres = $get('criteres');
        $score = $this->toFloat($get('score_2025'));
        $capacite = $this->toInt($get('capacite'));

        if ($specialite === '' && $licence !== '') $specialite = $licence;
        if ($licence === '' && $specialite !== '') $licence = $specialite;

        return [
            'licence' => $licence ?: null,
            'specialite' => $specialite ?: "Filière {$code}",
            'code' => $code,
            'universite' => $universite ?: null,
            'type_bac' => $typeBac ?: null,
            'formule' => $formule ?: null,
            'capacite' => $capacite,
            'score_dernier_oriente_2025' => $score,
            'criteres' => $criteres !== '' ? [$criteres] : null,
        ];
    }

    private function toFloat(string $v): ?float
    {
        $v = trim(str_replace(',', '.', $v));
        if ($v === '' || $v === '-') return null;
        return is_numeric($v) ? (float) $v : null;
    }

    private function toInt(string $v): ?int
    {
        $v = preg_replace('/\D+/', '', $v ?? '');
        if ($v === null || $v === '') return null;
        return (int) $v;
    }

    private function rowIsEmpty(array $row): bool
    {
        foreach ($row as $v) {
            if (trim((string) $v) !== '') return false;
        }
        return true;
    }
}

