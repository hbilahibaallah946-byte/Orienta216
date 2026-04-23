<?php

namespace App\Console\Commands;

use App\Models\Filiere;
use Illuminate\Console\Command;
use Smalot\PdfParser\Parser;

class FixFiliereColumnMapping extends Command
{
    protected $signature = 'filieres:fix-columns
                            {--pdf= : Chemin du PDF pour ré-extraire les التخصصات}
                            {--dry-run : Aperçu sans modifier}';

    protected $description = 'Corrige le mapping des colonnes: déplace specialite→type_bac et ré-extrait les vraies التخصصات du PDF';

    private const ARABIC_RE = '/[\x{0600}-\x{06FF}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}]/u';

    /**
     * Known bac types — if specialite matches one of these, it's actually the bac type.
     */
    private const BAC_TYPES = [
        'آداب', 'رياضيات', 'علوم تجريبية', 'إقتصاد وتصرف',
        'علوم الإعلامية', 'رياضة', 'تقنية', 'العلوم التقنية',
        'آداب)(+', 'آداب)+(',
        // French equivalents (from CSV import)
        'Bac Général', 'Bac Mathématiques', 'Bac Sciences', 'Bac Économie',
    ];

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $pdfPath = $this->option('pdf');

        // Step 1: Extract real specializations from PDF (if available)
        $pdfSpecializations = [];
        if ($pdfPath) {
            if (! is_readable($pdfPath)) {
                $this->error("PDF introuvable: {$pdfPath}");
                return self::FAILURE;
            }
            $this->info('Extraction des التخصصات depuis le PDF...');
            $pdfSpecializations = $this->extractSpecializationsFromPdf($pdfPath);
            $this->info('Codes avec spécialisation trouvés: ' . count($pdfSpecializations));
        } else {
            // Try to find the stored PDF
            $storedPdf = glob(storage_path('app/public/pdfs/*.pdf'));
            if (! empty($storedPdf)) {
                $pdfPath = $storedPdf[0];
                $this->info("PDF trouvé: {$pdfPath}");
                $pdfSpecializations = $this->extractSpecializationsFromPdf($pdfPath);
                $this->info('Codes avec spécialisation trouvés: ' . count($pdfSpecializations));
            } else {
                $this->warn('Aucun PDF fourni — seul le swap specialite→type_bac sera fait.');
            }
        }

        // Step 2: Fix each filiere
        $fixed = 0;
        $total = Filiere::count();

        foreach (Filiere::cursor() as $filiere) {
            $changes = [];
            $currentSpecialite = $filiere->specialite;
            $currentLicence = $filiere->licence;
            $currentTypeBac = $filiere->type_bac;
            $code = $filiere->code;

            // If type_bac is empty and specialite looks like a bac type, swap
            if (empty($currentTypeBac) && $this->looksLikeBacType($currentSpecialite)) {
                $changes['type_bac'] = $currentSpecialite;

                // Try to get real specialization from PDF
                if (isset($pdfSpecializations[$code]) && ! empty($pdfSpecializations[$code])) {
                    $realSpec = $pdfSpecializations[$code];
                    $changes['specialite'] = $realSpec;
                } else {
                    // No real spec from PDF — use université or code as fallback
                    // The specialite field cannot be null
                    $fallback = $filiere->universite ?: ("Filière {$code}");
                    $changes['specialite'] = $fallback;
                }
            }

            // If licence also looks like a bac type and we have PDF data, fix licence too
            if (! empty($changes) && $this->looksLikeBacType($currentLicence)) {
                if (isset($pdfSpecializations[$code . '_licence'])) {
                    $changes['licence'] = $pdfSpecializations[$code . '_licence'];
                }
                // If licence = old specialite (both bac type), and we have a real specialite now,
                // set licence to the real spec too
                if ($currentLicence === $currentSpecialite && isset($changes['specialite'])) {
                    // Keep licence as-is if it's different, otherwise null it
                }
            }

            if (! empty($changes)) {
                $fixed++;
                if ($dryRun) {
                    $this->line("#{$filiere->id} ({$code}):");
                    foreach ($changes as $field => $val) {
                        $old = $filiere->{$field} ?? '(null)';
                        $new = $val ?? '(null)';
                        $this->line("  {$field}: {$old}  →  {$new}");
                    }
                } else {
                    $filiere->update($changes);
                }
            }
        }

        if ($dryRun) {
            $this->info("[dry-run] {$fixed} filières seraient corrigées sur {$total}.");
        } else {
            $this->info("{$fixed} filières corrigées sur {$total}.");
        }

        return self::SUCCESS;
    }

    /**
     * Check if a string looks like a bac type rather than a real specialization.
     */
    private function looksLikeBacType(?string $value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        $v = trim($value);

        // Exact match
        foreach (self::BAC_TYPES as $bt) {
            if ($v === $bt) {
                return true;
            }
        }

        // Normalized match
        $vNorm = $this->normalizeForCompare($v);
        foreach (self::BAC_TYPES as $bt) {
            if ($this->normalizeForCompare($bt) === $vNorm) {
                return true;
            }
        }

        // Short Arabic text that's likely a bac type (آداب = 4 chars, رياضيات = 7, etc.)
        if (preg_match(self::ARABIC_RE, $v) && mb_strlen($v) <= 20) {
            $keywords = ['آداب', 'رياضي', 'علوم', 'إقتصاد', 'تصرف', 'إعلام', 'رياضة', 'تقني'];
            foreach ($keywords as $kw) {
                if (mb_strpos($v, $kw) !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    private function normalizeForCompare(string $v): string
    {
        $v = mb_strtolower(trim($v));
        if (class_exists(\Normalizer::class)) {
            $n = \Normalizer::normalize($v, \Normalizer::FORM_KC);
            if (is_string($n)) $v = $n;
        }
        return preg_replace('/\s+/u', ' ', $v) ?? $v;
    }

    /**
     * Extract code → specialization mapping from the PDF.
     * Identifies lines starting with - or – as specializations (التخصصات).
     */
    private function extractSpecializationsFromPdf(string $path): array
    {
        $parser = new Parser();
        $pdf = $parser->parseFile($path);
        $result = [];

        foreach ($pdf->getPages() as $page) {
            $text = $page->getText();
            $lines = preg_split('/\R/u', $text) ?: [];
            $lines = array_values(array_filter(array_map(fn($l) => trim((string) $l), $lines)));

            $codes = [];
            $specializations = [];
            $licenceLabels = [];

            foreach ($lines as $line) {
                // 5-digit codes
                if (preg_match('/^\d{5}$/', $line)) {
                    $codes[] = $line;
                    continue;
                }

                // Lines starting with - are specializations (التخصصات)
                if (preg_match('/^[\-–—]\s*(.+)$/u', $line, $m)) {
                    $val = trim($m[1]);
                    if ($val !== '' && mb_strlen($val) > 2) {
                        $specializations[] = $this->fixReversedArabic($line);
                    }
                    continue;
                }

                // الإجازة lines
                $lineNorm = $this->normalizeArabicNfkc($line);
                if (preg_match('/الإجازة/u', $lineNorm) || preg_match('/ﺍﻹﺟﺎﺯﺓ/u', $line)) {
                    $licenceLabels[] = $this->fixReversedArabic($line);
                    continue;
                }
            }

            // Map codes to specializations
            $specStr = implode("\n", $specializations);
            $licStr = ! empty($licenceLabels) ? $licenceLabels[0] : null;

            foreach ($codes as $i => $code) {
                if (! isset($result[$code]) || empty($result[$code])) {
                    $result[$code] = $specStr ?: null;
                }
                if ($licStr && ! isset($result[$code . '_licence'])) {
                    $result[$code . '_licence'] = $licStr;
                }
            }
        }

        return $result;
    }

    /**
     * Reverse Arabic text extracted in visual order by PDF parser.
     */
    private function fixReversedArabic(string $value): string
    {
        $v = trim($value);
        if ($v === '') return $v;

        if (! preg_match('/[\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}]/u', $v)) {
            return $v;
        }

        $chars = [];
        $len = mb_strlen($v, 'UTF-8');
        for ($i = 0; $i < $len; $i++) {
            $chars[] = mb_substr($v, $i, 1, 'UTF-8');
        }
        $reversed = implode('', array_reverse($chars));

        if (class_exists(\Normalizer::class)) {
            $n = \Normalizer::normalize($reversed, \Normalizer::FORM_KC);
            if (is_string($n) && $n !== '') {
                $reversed = $n;
            }
        }

        return trim($reversed);
    }

    private function normalizeArabicNfkc(string $v): string
    {
        if (class_exists(\Normalizer::class)) {
            $n = \Normalizer::normalize($v, \Normalizer::FORM_KC);
            if (is_string($n) && $n !== '') return $n;
        }
        return $v;
    }
}
