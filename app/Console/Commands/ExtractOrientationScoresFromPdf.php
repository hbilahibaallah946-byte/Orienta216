<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Smalot\PdfParser\Parser;

class ExtractOrientationScoresFromPdf extends Command
{
    protected $signature = 'filieres:extract-orientation-scores
                            {pdf : Chemin absolu du PDF SD_TN}
                            {output : Chemin du CSV de sortie}
                            {--strategy=min : min|max|avg|median}';

    protected $description = 'Extrait les scores 2025 depuis un PDF arabe orientation.tn vers un CSV (code, score_2025)';

    public function handle(): int
    {
        $pdfPath = (string) $this->argument('pdf');
        $outPath = (string) $this->argument('output');
        $strategy = strtolower((string) $this->option('strategy'));

        if (! in_array($strategy, ['min', 'max', 'avg', 'median'], true)) {
            $this->error("Option --strategy invalide: {$strategy} (min|max|avg|median)");
            return self::FAILURE;
        }

        if (! is_readable($pdfPath)) {
            $this->error("PDF introuvable/illisible: {$pdfPath}");
            return self::FAILURE;
        }

        $rows = $this->extractScoresByCode($pdfPath, $strategy);
        if ($rows === []) {
            $this->error('Aucun code/score exploitable trouvé dans le PDF.');
            return self::FAILURE;
        }

        $dir = dirname($outPath);
        if (! is_dir($dir) && ! @mkdir($dir, 0777, true) && ! is_dir($dir)) {
            $this->error("Impossible de créer le dossier de sortie: {$dir}");
            return self::FAILURE;
        }

        $fh = fopen($outPath, 'w');
        if ($fh === false) {
            $this->error("Impossible d'écrire le fichier: {$outPath}");
            return self::FAILURE;
        }

        fputcsv($fh, ['code', 'score_2025', 'samples_count', 'score_min', 'score_max'], ';');
        foreach ($rows as $r) {
            fputcsv($fh, [
                $r['code'],
                $r['score_2025'],
                $r['samples_count'],
                $r['score_min'],
                $r['score_max'],
            ], ';');
        }
        fclose($fh);

        $this->info('Extraction terminée: '.count($rows).' codes.');
        $this->info("CSV généré: {$outPath}");
        $this->line('Exemples:');
        foreach (array_slice($rows, 0, 8) as $r) {
            $this->line("{$r['code']} => {$r['score_2025']} (n={$r['samples_count']}, min={$r['score_min']}, max={$r['score_max']})");
        }

        return self::SUCCESS;
    }

    /**
     * @return array<int,array{code:string,score_2025:string,samples_count:int,score_min:string,score_max:string}>
     */
    private function extractScoresByCode(string $pdfPath, string $strategy): array
    {
        $parser = new Parser();
        $pdf = $parser->parseFile($pdfPath);

        /** @var array<string,list<float>> $scoresByCode */
        $scoresByCode = [];

        foreach ($pdf->getPages() as $page) {
            $text = $page->getText();
            $lines = preg_split('/\R/u', $text) ?: [];
            $lines = array_values(array_filter(array_map(
                static fn ($l) => trim((string) $l),
                $lines
            )));

            $codes = [];
            $scores = [];

            foreach ($lines as $line) {
                if (preg_match('/^\d{5}$/', $line)) {
                    $codes[] = $line;
                    continue;
                }

                // Ex: 122.105 | 89.0233
                if (preg_match('/^\d{2,3}(?:[.,]\d{1,4})$/', $line)) {
                    $v = (float) str_replace(',', '.', $line);
                    if ($v > 0) {
                        $scores[] = $v;
                    }
                }
            }

            $codeCount = count($codes);
            if ($codeCount === 0 || $scores === []) {
                continue;
            }

            // Répartition cyclique des valeurs: conserve tous les seuils de la page
            // puis agrège par code (min/max/avg/median).
            foreach ($scores as $i => $score) {
                $code = $codes[$i % $codeCount];
                $scoresByCode[$code] ??= [];
                $scoresByCode[$code][] = $score;
            }
        }

        $rows = [];
        foreach ($scoresByCode as $code => $values) {
            sort($values, SORT_NUMERIC);
            $min = $values[0];
            $max = $values[count($values) - 1];
            $avg = array_sum($values) / count($values);
            $median = $this->median($values);

            $chosen = match ($strategy) {
                'max' => $max,
                'avg' => $avg,
                'median' => $median,
                default => $min,
            };

            $rows[] = [
                'code' => $code,
                'score_2025' => number_format($chosen, 3, '.', ''),
                'samples_count' => count($values),
                'score_min' => number_format($min, 3, '.', ''),
                'score_max' => number_format($max, 3, '.', ''),
            ];
        }

        usort($rows, static fn ($a, $b) => strcmp($a['code'], $b['code']));
        return $rows;
    }

    /**
     * @param list<float> $values
     */
    private function median(array $values): float
    {
        $count = count($values);
        if ($count === 0) {
            return 0.0;
        }
        $mid = intdiv($count, 2);
        if ($count % 2 === 1) {
            return $values[$mid];
        }

        return ($values[$mid - 1] + $values[$mid]) / 2.0;
    }
}

