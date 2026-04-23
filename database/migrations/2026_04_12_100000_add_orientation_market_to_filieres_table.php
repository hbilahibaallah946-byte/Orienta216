<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('filieres', function (Blueprint $table) {
            if (! Schema::hasColumn('filieres', 'capacite')) {
                $table->unsignedInteger('capacite')->nullable()->after('annee');
            }
            if (! Schema::hasColumn('filieres', 'score_dernier_oriente_2025')) {
                $table->decimal('score_dernier_oriente_2025', 10, 3)->nullable()->after('capacite');
            }
            if (! Schema::hasColumn('filieres', 'score_dernier_oriente_2024')) {
                $table->decimal('score_dernier_oriente_2024', 10, 3)->nullable()->after('score_dernier_oriente_2025');
            }
            if (! Schema::hasColumn('filieres', 'score_dernier_oriente_2023')) {
                $table->decimal('score_dernier_oriente_2023', 10, 3)->nullable()->after('score_dernier_oriente_2024');
            }
        });
    }

    public function down(): void
    {
        Schema::table('filieres', function (Blueprint $table) {
            $cols = ['capacite', 'score_dernier_oriente_2025', 'score_dernier_oriente_2024', 'score_dernier_oriente_2023'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('filieres', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
