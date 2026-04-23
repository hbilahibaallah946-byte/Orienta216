<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recommandations', function (Blueprint $table) {
            if (! Schema::hasColumn('recommandations', 'score_academique')) {
                $table->unsignedTinyInteger('score_academique')->nullable()->after('score');
            }
            if (! Schema::hasColumn('recommandations', 'score_compatibilite')) {
                $table->unsignedTinyInteger('score_compatibilite')->nullable()->after('score_academique');
            }
            if (! Schema::hasColumn('recommandations', 'score_competitivite')) {
                $table->unsignedTinyInteger('score_competitivite')->nullable()->after('score_compatibilite');
            }
            if (! Schema::hasColumn('recommandations', 'score_reference_marche')) {
                $table->decimal('score_reference_marche', 10, 3)->nullable()->after('score_competitivite');
            }
            if (! Schema::hasColumn('recommandations', 'accessible_selon_bac')) {
                $table->boolean('accessible_selon_bac')->nullable()->after('score_reference_marche');
            }
        });
    }

    public function down(): void
    {
        Schema::table('recommandations', function (Blueprint $table) {
            foreach (['score_academique', 'score_compatibilite', 'score_competitivite', 'score_reference_marche', 'accessible_selon_bac'] as $col) {
                if (Schema::hasColumn('recommandations', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
