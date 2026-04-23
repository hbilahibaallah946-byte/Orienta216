<?php

namespace App\Console\Commands;

use App\Models\Filiere;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncPdfScoresIntoCsvFilieres extends Command
{
    protected $signature = 'filieres:sync-scores
                            {csv_path : Chemin du fichier CSV (français, propre)}
                            {pdf_path : Chemin du PDF orientation (arabe, avec scores)}
                            {--dry-run : Analyse sans écriture}';

    protected $description = 'Importe les filières depuis le CSV (données françaises) puis y greffe les scores (مجموع آخر موجه 2024) et capacités extraits du PDF arabe.';

    public function handle(): int
    {
        $csvPath = (string) $this->argument('csv_path');
        $pdfPath = (string) $this->argument('pdf_path');

        if (! is_readable($csvPath)) {
            $this->error("CSV introuvable: {$csvPath}");
            return self::FAILURE;
        }
        if (! is_readable($pdfPath)) {
            $this->error("PDF introuvable: {$pdfPath}");
            return self::FAILURE;
        }

        // 1. Parse PDF to extract code → score mapping
        $this->info('Extraction des scores depuis le PDF...');
        $pdfScores = $this->extractScoresFromPdf($pdfPath);
        $this->info('Codes avec score trouvés dans le PDF: ' . count($pdfScores));

        // 2. Parse CSV
        $this->info('Lecture du CSV...');
        $csvRows = $this->parseCsv($csvPath);
        if ($csvRows === []) {
            $this->error('Aucune ligne exploitable dans le CSV.');
            return self::FAILURE;
        }
        $this->info('Lignes CSV: ' . count($csvRows));

        // 3. Merge: for each CSV row, add score from PDF if available
        $merged = 0;
        foreach ($csvRows as &$row) {
            $code = $row['code'] ?? '';
            if ($code !== '' && isset($pdfScores[$code])) {
                $pdfData = $pdfScores[$code];
                if (($row['score_dernier_oriente_2025'] ?? null) === null && ($pdfData['score'] ?? null) !== null) {
                    $row['score_dernier_oriente_2025'] = $pdfData['score'];
                    $merged++;
                }
                if (($row['capacite'] ?? null) === null && ($pdfData['capacite'] ?? null) !== null) {
                    $row['capacite'] = $pdfData['capacite'];
                }
            }
        }
        unset($row);

        $this->info("Scores PDF greffés sur {$merged} filières CSV.");

        if ($this->option('dry-run')) {
            $this->table(
                ['Code', 'Licence', 'Spécialité', 'Université', 'Score 2024'],
                array_map(fn($r) => [
                    $r['code'],
                    mb_substr($r['licence'] ?? '', 0, 30),
                    mb_substr($r['specialite'] ?? '', 0, 30),
                    mb_substr($r['universite'] ?? '', 0, 30),
                    $r['score_dernier_oriente_2025'] ?? '-',
                ], array_slice($csvRows, 0, 20))
            );
            return self::SUCCESS;
        }

        // 4. Replace in DB
        DB::transaction(function () use ($csvRows) {
            Filiere::query()->delete();
            foreach ($csvRows as $row) {
                Filiere::create($row);
            }
        });

        $this->info('Remplacement terminé: ' . count($csvRows) . ' filières importées (dont ' . $merged . ' avec score PDF).');
        return self::SUCCESS;
    }

    /**
     * Extract code → {score, capacite} from PDF
     */
    private function extractScoresFromPdf(string $path): array
    {
        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile($path);

        $codeScores = [];

        foreach ($pdf->getPages() as $page) {
            $text = $page->getText();
            $lines = preg_split('/\R/u', $text) ?: [];
            $lines = array_values(array_filter(array_map(fn($l) => trim((string) $l), $lines)));

            $codes = [];
            $scores = [];
            $capacites = [];

            foreach ($lines as $line) {
                // 5-digit codes
                if (preg_match('/^\d{5}$/', $line)) {
                    $codes[] = $line;
                    continue;
                }

                // Scores like 97.88, 130.94, etc.
                if (preg_match('/^\d{2,3}\.\d{1,3}$/', $line)) {
                    $scores[] = (float) $line;
                    continue;
                }

                // Capacités (small numbers)
                if (preg_match('/^\d{1,3}$/', $line)) {
                    $n = (int) $line;
                    if ($n > 0 && $n <= 500) {
                        $capacites[] = $n;
                    }
                    continue;
                }
            }

            // Map codes to scores
            $maxCodes = count($codes);
            for ($i = 0; $i < $maxCodes; $i++) {
                $code = $codes[$i];
                if (isset($codeScores[$code])) continue;

                $codeScores[$code] = [
                    'score' => $scores[$i] ?? ($scores[0] ?? null),
                    'capacite' => $capacites[$i] ?? ($capacites[0] ?? null),
                ];
            }
        }

        return $codeScores;
    }

    /**
     * Parse CSV (reuses ReplaceFilieresFromCsv logic)
     */
    private function parseCsv(string $path): array
    {
        $delimiter = $this->guessDelimiter($path);
        $fh = fopen($path, 'r');
        if (! $fh) return [];

        $header = fgetcsv($fh, 0, $delimiter);
        if (! $header) {
            fclose($fh);
            return [];
        }

        $map = $this->buildHeaderMap($header);
        if (! isset($map['code'])) {
            fclose($fh);
            $this->error("Colonne Code obligatoire dans le CSV.");
            return [];
        }

        $rows = [];
        while (($r = fgetcsv($fh, 0, $delimiter)) !== false) {
            if ($this->rowIsEmpty($r)) continue;
            $row = $this->mapRow($r, $map);
            if (! $row || empty($row['code'])) continue;
            $rows[$row['code']] = $row;
        }
        fclose($fh);

        return array_values($rows);
    }

    private function guessDelimiter(string $path): string
    {
        $sample = file_get_contents($path, false, null, 0, 4096) ?: '';
        return substr_count($sample, ';') > substr_count($sample, ',') ? ';' : ',';
    }

    private function normalizeHeader(string $h): string
    {
        $h = mb_strtolower(trim($h));
        $h = str_replace(['(', ')', "\u{2018}", "'", '"', 'é', 'è', 'ê', 'à', 'û', 'î', 'ï', 'ô', 'ù', '/', '-'], [' ', ' ', ' ', ' ', ' ', 'e', 'e', 'e', 'a', 'u', 'i', 'i', 'o', 'u', ' ', ' '], $h);
        $h = preg_replace('/\s+/', ' ', $h) ?? $h;
        return trim($h);
    }

    private function buildHeaderMap(array $header): array
    {
        $m = [];
        foreach ($header as $i => $hRaw) {
            $h = $this->normalizeHeader((string) $hRaw);

            if (str_contains($h, 'filiere') || str_contains($h, 'licence')) $m['licence'] = $i;
            if ($h === 'code' || str_contains($h, 'code')) $m['code'] = $i;
            if (str_contains($h, 'etablissement') || str_contains($h, 'universite')) $m['universite'] = $i;
            if (str_contains($h, 'parcours') || str_contains($h, 'specialite')) $m['specialite'] = $i;
            if (str_contains($h, 'serie') || str_contains($h, 'type_bac') || str_contains($h, 'type bac')) $m['type_bac'] = $i;
            if (str_contains($h, 'formule du score')) $m['formule_score'] = $i;
            if (str_contains($h, 'conditions') || str_contains($h, 'formule')) $m['conditions'] = $i;
            if (str_contains($h, 'bandeau') || str_contains($h, 'critere')) $m['criteres'] = $i;
            if (str_contains($h, 'score dernier oriente') || str_contains($h, 'moyenne dernier') || str_contains($h, '2024')) $m['score_2025'] = $i;
            if (str_contains($h, 'capacite') || str_contains($h, 'energie') || str_contains($h, 'places')) $m['capacite'] = $i;
        }
        return $m;
    }

    private function mapRow(array $r, array $m): ?array
    {
        $get = fn(string $k) => isset($m[$k]) ? trim((string) ($r[$m[$k]] ?? '')) : '';
        $code = $get('code');
        if ($code === '') return null;

        $licence = $get('licence');
        $specialite = $get('specialite');
        $universite = $get('universite');
        $typeBac = $get('type_bac');
        $fScore = $get('formule_score');
        $fCond = $get('conditions');
        $formule = trim(implode(' | ', array_values(array_filter([$fScore, $fCond], fn($x) => $x !== ''))));
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
