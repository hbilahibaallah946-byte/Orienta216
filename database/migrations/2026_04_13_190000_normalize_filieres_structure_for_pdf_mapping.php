<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('filieres', function (Blueprint $table) {
            if (! Schema::hasColumn('filieres', 'licence')) {
                $table->string('licence')->nullable()->after('specialite');
            }
        });

        Schema::table('filieres', function (Blueprint $table) {
            if (Schema::hasColumn('filieres', 'score_dernier_oriente_2024')) {
                $table->dropColumn('score_dernier_oriente_2024');
            }
            if (Schema::hasColumn('filieres', 'score_dernier_oriente_2023')) {
                $table->dropColumn('score_dernier_oriente_2023');
            }
            if (Schema::hasColumn('filieres', 'annee')) {
                $table->dropColumn('annee');
            }
        });
    }

    public function down(): void
    {
        Schema::table('filieres', function (Blueprint $table) {
            if (! Schema::hasColumn('filieres', 'annee')) {
                $table->integer('annee')->nullable();
            }
            if (! Schema::hasColumn('filieres', 'score_dernier_oriente_2024')) {
                $table->decimal('score_dernier_oriente_2024', 10, 3)->nullable();
            }
            if (! Schema::hasColumn('filieres', 'score_dernier_oriente_2023')) {
                $table->decimal('score_dernier_oriente_2023', 10, 3)->nullable();
            }
            if (Schema::hasColumn('filieres', 'licence')) {
                $table->dropColumn('licence');
            }
        });
    }
};

