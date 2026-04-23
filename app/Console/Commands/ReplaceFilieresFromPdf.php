<?php

namespace App\Console\Commands;

use App\Models\Filiere;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Smalot\PdfParser\Parser;

class ReplaceFilieresFromPdf extends Command
{
    protected $signature = 'filieres:replace-from-pdf
                            {path : Chemin absolu du PDF}
                            {--dry-run : Analyse sans écrire en base}';

    protected $description = 'Nettoie un PDF orientation.tn et remplace toutes les filières existantes';

    public function handle(): int
    {
        $path = (string) $this->argument('path');
        if (! is_readable($path)) {
            $this->error("PDF introuvable ou illisible: {$path}");
            return self::FAILURE;
        }

        $rows = $this->extractRows($path);
        if ($rows === []) {
            $this->error("Aucune filière exploitable détectée dans le PDF.");
            return self::FAILURE;
        }

        $this->info('Filières détectées: '.count($rows));
        if ($this->option('dry-run')) {
            foreach (array_slice($rows, 0, 20) as $r) {
                $this->line(sprintf(
                    "%s | licence: %s | spec: %s | univ: %s | bac: %s | cap: %s | score: %s",
                    $r['code'],
                    mb_substr($r['licence'] ?? '-', 0, 35),
                    mb_substr($r['specialite'] ?? '-', 0, 35),
                    mb_substr($r['universite'] ?? '-', 0, 35),
                    $r['type_bac'] ?? '-',
                    $r['capacite'] ?? '-',
                    $r['score_dernier_oriente_2025'] ?? '-'
                ));
            }
            $this->line('Dry-run: aucune écriture en base.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($rows) {
            Filiere::query()->delete();
            foreach ($rows as $row) {
                Filiere::create($row);
            }
        });

        $this->info('Remplacement terminé. '.count($rows).' filières importées.');
        return self::SUCCESS;
    }

    /**
     * Extraction adaptée au guide orientation.tn.
     *
     * Colonnes du PDF (droite à gauche) :
     *   الإجازة / الشعبة | المؤسسة والجامعة | التخصصات | الرمز | نوع الباكالوريا | صيغة | طاقة الاستيعاب | مجموع آخر موجه 2024
     *
     * @return array<int,array<string,mixed>>
     */
    private function extractRows(string $path): array
    {
        $parser = new Parser();
        $pdf    = $parser->parseFile($path);

        $rowsByCode = [];

        foreach ($pdf->getPages() as $page) {
            $text  = $page->getText();
            $lines = preg_split('/\R/u', $text) ?: [];
            $lines = array_values(array_filter(array_map(fn ($l) => trim((string) $l), $lines)));

            // ── Classify each line ──────────────────────────────────────
            $codes          = [];  // الرمز
            $specialites    = [];  // التخصصات (lines starting with -)
            $licences       = [];  // الإجازة / الشعبة
            $institutions   = [];  // المؤسسة والجامعة
            $formules       = [];  // صيغة
            $bacTypes       = [];  // نوع الباكالوريا
            $scores         = [];  // مجموع آخر موجه 2024
            $capacites      = [];  // طاقة الاستيعاب
            $criteriaHeading = null;

            foreach ($lines as $line) {
                $lineNorm = $this->normalizeArabicText($line);

                // ─── 1. Codes (5-digit) ─────────────────────────────────
                if (preg_match('/^\d{5}$/', $line)) {
                    $codes[] = $line;
                    continue;
                }

                // ─── 2. Scores (xx.xx or xxx.xxx) ───────────────────────
                if (preg_match('/^\d{2,3}\.\d{1,3}$/', $line)) {
                    $scores[] = (float) $line;
                    continue;
                }

                // ─── 3. Formules (FG+AR, etc.) ──────────────────────────
                if (preg_match('/^(FG|PH|AR|M|SVT|PC|T|ECO|INFO|ANG|S\b)/u', $line)) {
                    $formules[] = preg_replace('/\s+/', '', $line);
                    continue;
                }

                // ─── 4. Skip table header rows ──────────────────────────
                if ($this->isTableHeader($line, $lineNorm)) {
                    continue;
                }

                // ─── 5. Bandeau / criteria heading ──────────────────────
                if ($criteriaHeading === null && $this->isCriteriaHeading($line, $lineNorm)) {
                    $criteriaHeading = $line;
                    continue;
                }

                // ─── 6. نوع الباكالوريا — MUST come BEFORE fallback ────
                if ($this->isBacType($line, $lineNorm)) {
                    $bacTypes[] = $line;
                    continue;
                }

                // ─── 7. الإجازة / الشعبة ────────────────────────────────
                if ($this->isLicenceLine($line, $lineNorm)) {
                    $licences[] = $line;
                    continue;
                }

                // ─── 8. المؤسسة والجامعة ────────────────────────────────
                if ($this->isInstitutionLine($line, $lineNorm)) {
                    $institutions[] = $line;
                    continue;
                }

                // ─── 9. التخصصات (lines starting with -) ───────────────
                if (preg_match('/^[\-\x{2013}\x{2014}]\s*(.+)$/u', $line, $m)) {
                    $val = trim($m[0]); // Keep the dash prefix
                    if ($val !== '' && mb_strlen($val) > 2) {
                        $specialites[] = $val;
                    }
                    continue;
                }

                // ─── 10. Capacités (small numbers 1-500) ────────────────
                if (preg_match('/^\d{1,3}$/', $line)) {
                    $n = (int) $line;
                    if ($n > 0 && $n <= 500) {
                        $capacites[] = $n;
                    }
                    continue;
                }

                // ─── 11. Remaining long Arabic text → specializations ───
                if (preg_match('/[\x{0600}-\x{06FF}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}]/u', $line)
                    && mb_strlen($line) > 20
                    && ! $this->isBacType($line, $lineNorm)
                    && ! $this->isInstitutionLine($line, $lineNorm)) {
                    $specialites[] = $line;
                }
            }

            if ($codes === []) {
                continue;
            }

            // ── Build rows: map columns to codes ────────────────────────
            $max = count($codes);
            for ($i = 0; $i < $max; $i++) {
                $code = $codes[$i];
                if (isset($rowsByCode[$code])) {
                    continue;
                }

                // التخصصات — all specializations on this page belong to all codes
                $specialiteRaw = null;
                if (count($specialites) > 0) {
                    if (count($specialites) >= $max) {
                        $specialiteRaw = $specialites[$i] ?? null;
                    } else {
                        // All belong to all codes
                        $specialiteRaw = implode("\n", $specialites);
                    }
                }

                // الإجازة / الشعبة — usually one per page section
                $licenceRaw = $licences[0] ?? null;
                if (count($licences) > 1 && isset($licences[$i])) {
                    $licenceRaw = $licences[$i];
                }

                // المؤسسة — distribute among codes
                $universiteRaw = null;
                if (count($institutions) === 1) {
                    $universiteRaw = $institutions[0];
                } elseif (count($institutions) >= $max) {
                    $universiteRaw = $institutions[$i];
                } elseif (count($institutions) > 0) {
                    $universiteRaw = $institutions[$i % count($institutions)];
                }

                // نوع الباكالوريا — distribute among codes
                $typeBacRaw = null;
                if (count($bacTypes) === 1) {
                    $typeBacRaw = $bacTypes[0];
                } elseif (count($bacTypes) >= $max) {
                    $typeBacRaw = $bacTypes[$i];
                } elseif (count($bacTypes) > 0) {
                    $typeBacRaw = $bacTypes[$i % count($bacTypes)];
                }

                // Normalize all Arabic text (reverse + NFKC)
                $specialite = $this->normalizeArabicLabel($specialiteRaw);
                $licence    = $this->normalizeArabicLabel($licenceRaw);
                $universite = $this->normalizeArabicLabel($universiteRaw);
                $typeBac    = $this->normalizeArabicLabel($typeBacRaw);

                $formule  = $formules !== [] ? $formules[$i % count($formules)] : null;
                $capacite = $capacites !== [] ? $capacites[$i % count($capacites)] : null;
                $score    = $scores !== [] ? $scores[$i % count($scores)] : null;

                $candidate = [
                    'licence'    => $licence,
                    'specialite' => $specialite ?: $licence ?: "Filière {$code}",
                    'code'       => $code,
                    'universite' => $universite,
                    'type_bac'   => $typeBac,
                    'formule'    => $formule,
                    'capacite'   => $capacite,
                    'score_dernier_oriente_2025' => $score,
                    'criteres'   => $criteriaHeading
                        ? [$this->mapCriteriaHeadingToFrench($criteriaHeading)]
                        : null,
                ];

                if (! isset($rowsByCode[$code])) {
                    $rowsByCode[$code] = $candidate;
                    continue;
                }

                // Merge: keep the richest version
                $existing = $rowsByCode[$code];
                $existingQ  = $this->rowQuality($existing);
                $candidateQ = $this->rowQuality($candidate);

                if ($candidateQ > $existingQ) {
                    $rowsByCode[$code] = $candidate;
                } else {
                    foreach (['licence', 'specialite', 'universite', 'type_bac', 'formule', 'capacite', 'score_dernier_oriente_2025', 'criteres'] as $key) {
                        if (($existing[$key] ?? null) === null && ($candidate[$key] ?? null) !== null) {
                            $existing[$key] = $candidate[$key];
                        }
                    }
                    $rowsByCode[$code] = $existing;
                }
            }
        }

        return array_values($rowsByCode);
    }

    // ═══════════════════════════════════════════════════════════════════
    //  Line classification helpers
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Detect bac type lines: آداب, رياضيات, علوم تجريبية, إقتصاد وتصرف, etc.
     */
    private function isBacType(string $line, string $lineNorm): bool
    {
        // Presentation forms (raw PDF text)
        $pfPatterns = [
            '/^[\(\+\s]*\x{FE81}\x{FE93}\x{FE8D}\x{FE91}/u',  // آداب
            '/\x{FE8D}\x{FEDB}\x{FE8D}\x{FEDB}/u',             // generic presentation forms
        ];

        // Known bac types (normalized)
        $bacKeywords = [
            "\xD8\xA2\xD8\xAF\xD8\xA7\xD8\xA8",              // آداب
            "\xD8\xB1\xD9\x8A\xD8\xA7\xD8\xB6\xD9\x8A\xD8\xA7\xD8\xAA", // رياضيات
            "\xD8\xB9\xD9\x84\xD9\x88\xD9\x85 \xD8\xAA\xD8\xAC\xD8\xB1\xD9\x8A\xD8\xA8\xD9\x8A\xD8\xA9", // علوم تجريبية
            "\xD8\xA5\xD9\x82\xD8\xAA\xD8\xB5\xD8\xA7\xD8\xAF", // إقتصاد
            "\xD8\xB1\xD9\x8A\xD8\xA7\xD8\xB6\xD8\xA9",      // رياضة
            "\xD8\xA7\xD9\x84\xD8\xB9\xD9\x84\xD9\x88\xD9\x85 \xD8\xA7\xD9\x84\xD8\xAA\xD9\x82\xD9\x86\xD9\x8A\xD8\xA9", // العلوم التقنية
            "\xD8\xB9\xD9\x84\xD9\x88\xD9\x85 \xD8\xA7\xD9\x84\xD8\xA5\xD8\xB9\xD9\x84\xD8\xA7\xD9\x85\xD9\x8A\xD8\xA9", // علوم الإعلامية
        ];

        // Check with normalized text
        foreach ($bacKeywords as $kw) {
            if (mb_strpos($lineNorm, $kw) !== false && mb_strlen($lineNorm) <= 30) {
                return true;
            }
        }

        // Check raw PDF presentation forms
        if (preg_match('/^[\(\+\s]*(ﺁﺩﺍﺏ|ﺭﻳﺎﺿﻴﺎﺕ|ﻋﻠﻮﻡ|ﺍﻟﻌﻠﻮﻡ|ﺇﻗﺘﺼﺎﺩ|ﺭﻳﺎﺿﺔ|ﺗﻘﻨﻴﺔ)/u', $line)) {
            return true;
        }

        return false;
    }

    /**
     * Detect الإجازة / الشعبة lines.
     */
    private function isLicenceLine(string $line, string $lineNorm): bool
    {
        return preg_match('/\x{0627}\x{0644}\x{0625}\x{062C}\x{0627}\x{0632}\x{0629}/u', $lineNorm) === 1 // الإجازة
            || preg_match('/ﺍﻹﺟﺎﺯﺓ/u', $line) === 1
            || preg_match('/\x{0627}\x{0644}\x{0634}\x{0639}\x{0628}\x{0629}/u', $lineNorm) === 1; // الشعبة
    }

    /**
     * Detect institution/university lines.
     */
    private function isInstitutionLine(string $line, string $lineNorm): bool
    {
        $keywords = [
            "\xD8\xAC\xD8\xA7\xD9\x85\xD8\xB9\xD8\xA9",     // جامعة
            "\xD9\x83\xD9\x84\xD9\x8A\xD8\xA9",               // كلية
            "\xD8\xA7\xD9\x84\xD9\x85\xD8\xB9\xD9\x87\xD8\xAF", // المعهد
            "\xD9\x85\xD8\xA4\xD8\xB3\xD8\xB3\xD8\xA9",       // مؤسسة
            "\xD8\xA7\xD9\x84\xD9\x85\xD8\xAF\xD8\xB1\xD8\xB3\xD8\xA9", // المدرسة
        ];
        foreach ($keywords as $kw) {
            if (mb_strpos($lineNorm, $kw) !== false) return true;
        }
        // Also check raw presentation forms
        if (str_contains($line, 'ﺟﺎﻣﻌﺔ') || str_contains($line, 'ﻛﻠﻴﺔ')
            || str_contains($line, 'ﺍﻟﻤﻌﻬﺪ') || str_contains($line, 'ﻣﺆﺳﺴﺔ')
            || str_contains($line, 'ﺍﻟﻤﺪﺭﺳﺔ')) {
            return true;
        }
        return false;
    }

    /**
     * Detect table header rows (skip them).
     */
    private function isTableHeader(string $line, string $lineNorm): bool
    {
        $headers = [
            'المؤسسة', 'ﺍﻟﻤﺆﺳﺴﺔ',
            'الإجازة / الشعبة', 'ﺍﻹﺟﺎﺯﺓ / ﺍﻟﺸﻌﺒﺔ',
            'نوع الباكالوريا', 'ﻧﻮﻉ ﺍﻟﺒﺎﻛﺎﻟﻮﺭﻳﺎ',
            'صيغة احتساب', 'ﺻﻴﻐﺔ',
            'طاقة الاستيعاب', 'ﻃﺎﻗﺔ ﺍﻻﺳﺘﻴﻌﺎﺏ',
            'مجموع آخر موجه', 'ﻣﺠﻤﻮﻉ ﺍﺧﺮ',
            'التخصصات', 'ﺍﻟﺘﺨﺼﺼﺎﺕ',
            'الرمز', 'ﺍﻟﺮﻣﺰ',
        ];
        foreach ($headers as $h) {
            if (str_contains($line, $h) || str_contains($lineNorm, $h)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Detect bandeau criteria heading.
     */
    private function isCriteriaHeading(string $line, string $lineNorm): bool
    {
        if (preg_match('/\d/u', $line)) return false;
        if (mb_strlen($line) <= 12) return false;

        $markers = [
            'ﺍﻵﺩﺍﺏ', 'الآداب', 'ﺍﻟﻌﻠﻮﻡ', 'العلوم',
            'الاقتصاد', 'القانون', 'الهندسة', 'الفنون',
            'الصحة', 'المعلوماتية', 'التكنولوج',
            'الرياضية', 'التحضيرية',
        ];
        foreach ($markers as $m) {
            if (str_contains($line, $m) || str_contains($lineNorm, $m)) {
                return true;
            }
        }
        return false;
    }

    // ═══════════════════════════════════════════════════════════════════
    //  Text normalization
    // ═══════════════════════════════════════════════════════════════════

    /**
     * Normalize an Arabic label: fix reversed text + NFKC.
     * Returns the Arabic string as-is (no translation to French).
     */
    private function normalizeArabicLabel(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }
        $v = trim($value);

        // Fix reversed Arabic from PDF extraction
        $v = $this->fixReversedArabic($v);

        // NFKC normalization
        if (class_exists(\Normalizer::class)) {
            $n = \Normalizer::normalize($v, \Normalizer::FORM_KC);
            if (is_string($n) && $n !== '') {
                $v = $n;
            }
        }

        return trim($v);
    }

    private function rowQuality(array $row): int
    {
        $score = 0;
        foreach (['licence', 'specialite', 'universite', 'type_bac', 'formule', 'capacite', 'score_dernier_oriente_2025', 'criteres'] as $k) {
            $v = $row[$k] ?? null;
            if ($v === null) continue;
            if (is_array($v) && $v === []) continue;
            if (is_string($v) && trim($v) === '') continue;
            $score++;
        }
        return $score;
    }

    private function mapCriteriaHeadingToFrench(string $value): string
    {
        $v = trim($value);
        if ($v === '') return $v;

        $vNorm = $this->normalizeArabicText($v);

        $map = [
            'الآداب واللغات والمراحل التحضيرية الأدبية' => 'Lettres, langues et cycles préparatoires littéraires',
            'العلوم الإنسانية والاجتماعية والدينية والتربية' => 'Sciences humaines, sociales, religieuses et éducation',
            'العلوم القانونية والسياسية' => 'Sciences juridiques et politiques',
            'الاقتصاد والتصرف' => 'Économie et gestion',
            'العلوم الأساسية والتطبيقية' => 'Sciences fondamentales et appliquées',
            'العلوم الطبية والصيدلانية' => 'Sciences médicales et pharmaceutiques',
            'الهندسة المعمارية والتعمير' => 'Architecture et urbanisme',
            'الفنون والتصميم' => 'Arts et design',
            'علوم وتقنيات الأنشطة البدنية والرياضية' => 'STAPS',
            'الصحة العمومية' => 'Santé publique',
            'المراحل التحضيرية العلمية' => 'Cycles préparatoires scientifiques',
            'العلوم التكنولوجية' => 'Sciences technologiques',
            'المعلوماتية والاتصالات' => 'Informatique et communications',
        ];

        foreach ($map as $ar => $fr) {
            $arNorm = $this->normalizeArabicText($ar);
            if ($arNorm === $vNorm || mb_strpos($vNorm, $arNorm) !== false) {
                return $fr;
            }
        }

        $partialMap = [
            'آداب' => 'Lettres, langues et cycles préparatoires littéraires',
            'اداب' => 'Lettres, langues et cycles préparatoires littéraires',
            'قانون' => 'Sciences juridiques et politiques',
            'اقتصاد' => 'Économie et gestion',
            'طب' => 'Sciences médicales et pharmaceutiques',
            'هندسة' => 'Architecture et urbanisme',
            'فنون' => 'Arts et design',
            'رياض' => 'STAPS',
            'صحة' => 'Santé publique',
            'تحضيرية' => 'Cycles préparatoires scientifiques',
            'تكنولوج' => 'Sciences technologiques',
            'معلومات' => 'Informatique et communications',
            'إنسانية' => 'Sciences humaines, sociales, religieuses et éducation',
        ];

        foreach ($partialMap as $keyword => $fr) {
            if (mb_strpos($vNorm, $keyword) !== false) {
                return $fr;
            }
        }

        return $v;
    }

    /**
     * For backward compatibility — normalize + rudimentary translation.
     * New imports keep Arabic; this is used for criteria heading matching only.
     */
    private function normalizeFrenchLabel(?string $value, string $context): ?string
    {
        return $this->normalizeArabicLabel($value);
    }

    private function normalizeArabicText(string $value): string
    {
        $v = trim($value);
        if ($v === '') return $v;

        $v = $this->fixReversedArabic($v);

        if (class_exists(\Normalizer::class)) {
            $n = \Normalizer::normalize($v, \Normalizer::FORM_KC);
            if (is_string($n) && $n !== '') {
                $v = $n;
            }
        }

        return $v;
    }

    /**
     * PDF parsers extract Arabic text in visual order (LTR) instead of logical (RTL).
     * Detects reversed Arabic Presentation Forms and reverses them.
     */
    private function fixReversedArabic(string $value): string
    {
        $v = trim($value);
        if ($v === '') return $v;

        if (! preg_match('/[\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}]/u', $v)) {
            return $v; // No presentation forms → already correct
        }

        // Split into segments: Arabic vs non-Arabic
        $segments = preg_split('/([\x{0600}-\x{06FF}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}\x{064B}-\x{065F}\x{0670}\s]+)/u', $v, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        if ($segments === false) return $v;

        $result = '';
        $reversedSegments = array_reverse($segments);

        foreach ($reversedSegments as $seg) {
            if (preg_match('/[\x{0600}-\x{06FF}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}]/u', $seg)) {
                $graphemes = $this->splitGraphemes($seg);
                $result .= implode('', array_reverse($graphemes));
            } else {
                $result .= $seg;
            }
        }

        return trim($result);
    }

    private function splitGraphemes(string $str): array
    {
        if (function_exists('grapheme_strlen')) {
            $len = grapheme_strlen($str);
            $clusters = [];
            for ($i = 0; $i < $len; $i++) {
                $c = grapheme_substr($str, $i, 1);
                if ($c !== false && $c !== '') {
                    $clusters[] = $c;
                }
            }
            return $clusters;
        }

        $len = mb_strlen($str, 'UTF-8');
        $chars = [];
        for ($i = 0; $i < $len; $i++) {
            $chars[] = mb_substr($str, $i, 1, 'UTF-8');
        }
        return $chars;
    }

    /**
     * @deprecated Use normalizeArabicLabel instead
     */
    private function mapBacTypeToFrench(?string $value): ?string
    {
        return $this->normalizeArabicLabel($value);
    }
}
