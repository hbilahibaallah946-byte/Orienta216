<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SyncOrientationScoresFromGuide extends Command
{
    protected $signature = 'filieres:sync-scores-from-guide
                            {pdf : Chemin absolu du guide PDF arabe}
                            {--strategy=min : min|max|avg|median}';

    protected $description = 'Pipeline unique: extraction des scores 2025 du guide PDF puis import dans les filières existantes';

    public function handle(): int
    {
        $pdfPath = (string) $this->argument('pdf');
        $strategy = strtolower((string) $this->option('strategy'));

        if (! is_readable($pdfPath)) {
            $this->error("PDF introuvable/illisible: {$pdfPath}");
            return self::FAILURE;
        }

        $outputCsv = database_path('data/orientation_scores_2025_full_guide.csv');

        $this->info('1/2 Extraction des scores depuis le PDF...');
        $extractCode = Artisan::call('filieres:extract-orientation-scores', [
            'pdf' => $pdfPath,
            'output' => $outputCsv,
            '--strategy' => $strategy,
        ]);
        $this->line(Artisan::output());
        if ($extractCode !== self::SUCCESS) {
            $this->error('Echec pendant l’extraction.');
            return self::FAILURE;
        }

        $this->info('2/2 Import des scores dans les filières...');
        $importCode = Artisan::call('filieres:import-orientation-scores', [
            'path' => $outputCsv,
        ]);
        $this->line(Artisan::output());
        if ($importCode !== self::SUCCESS) {
            $this->error('Echec pendant l’import.');
            return self::FAILURE;
        }

        $this->info('Synchronisation terminée avec succès.');
        $this->info("CSV généré: {$outputCsv}");

        return self::SUCCESS;
    }
}

