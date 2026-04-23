<?php

namespace App\Console\Commands;

use App\Models\Filiere;
use Illuminate\Console\Command;

class FixReversedArabicInFilieres extends Command
{
    protected $signature = 'filieres:fix-arabic {--dry-run : Affiche sans modifier}';
    protected $description = 'Corrige le texte arabe inversé (extrait de PDF) dans toutes les filières';

    /**
     * Arabic Unicode ranges:
     *  - Arabic block:            U+0600 – U+06FF
     *  - Arabic Supplement:       U+0750 – U+077F
     *  - Arabic Pres. Forms-A:    U+FB50 – U+FDFF
     *  - Arabic Pres. Forms-B:    U+FE70 – U+FEFF
     */
    private const ARABIC_RE = '/[\x{0600}-\x{06FF}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}]/u';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $fields = ['specialite', 'licence', 'universite', 'type_bac'];

        $fixed = 0;
        $total = Filiere::count();
        $this->info("Analyse de {$total} filières…");

        foreach (Filiere::cursor() as $filiere) {
            $changes = [];

            foreach ($fields as $field) {
                $original = $filiere->{$field};
                if ($original === null || $original === '') {
                    continue;
                }

                // Only process fields that contain Arabic characters
                if (! preg_match(self::ARABIC_RE, $original)) {
                    continue;
                }

                $corrected = $this->fixReversedArabic($original);

                if ($corrected !== $original) {
                    $changes[$field] = $corrected;
                }
            }

            // Also fix criteres (JSON array)
            $criteres = $filiere->criteres;
            if (is_array($criteres) && ! empty($criteres)) {
                $newCriteres = [];
                $changed = false;
                foreach ($criteres as $c) {
                    if (is_string($c) && preg_match(self::ARABIC_RE, $c)) {
                        $fixedC = $this->fixReversedArabic($c);
                        $newCriteres[] = $fixedC;
                        if ($fixedC !== $c) $changed = true;
                    } else {
                        $newCriteres[] = $c;
                    }
                }
                if ($changed) {
                    $changes['criteres'] = $newCriteres;
                }
            }

            if (! empty($changes)) {
                $fixed++;
                if ($dryRun) {
                    $this->line("#{$filiere->id} ({$filiere->code}):");
                    foreach ($changes as $field => $val) {
                        $dispVal = is_array($val) ? json_encode($val, JSON_UNESCAPED_UNICODE) : $val;
                        $this->line("  {$field}: {$filiere->{$field}}  →  {$dispVal}");
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
     * Reverse Arabic text that was extracted in visual (LTR) order by PDF parsers.
     *
     * Strategy: reverse the entire string at grapheme level, then NFKC-normalize
     * to convert Arabic Presentation Forms → standard Arabic letters.
     */
    private function fixReversedArabic(string $value): string
    {
        $v = trim($value);
        if ($v === '') {
            return $v;
        }

        // Check if string has Arabic Presentation Forms (sign of PDF extraction)
        if (! preg_match('/[\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}]/u', $v)) {
            // No presentation forms → probably already correct
            return $v;
        }

        // Step 1: Reverse entire string at the character level
        $chars = $this->mbStrSplit($v);
        $reversed = implode('', array_reverse($chars));

        // Step 2: NFKC normalize (converts presentation forms → standard Arabic)
        if (class_exists(\Normalizer::class)) {
            $normalized = \Normalizer::normalize($reversed, \Normalizer::FORM_KC);
            if (is_string($normalized) && $normalized !== '') {
                $reversed = $normalized;
            }
        }

        return trim($reversed);
    }

    /**
     * Split a UTF-8 string into individual characters.
     */
    private function mbStrSplit(string $str): array
    {
        $len = mb_strlen($str, 'UTF-8');
        $chars = [];
        for ($i = 0; $i < $len; $i++) {
            $chars[] = mb_substr($str, $i, 1, 'UTF-8');
        }
        return $chars;
    }
}
