<?php
// database/migrations/2026_03_17_xxxxxx_update_filieres_table_clean.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('filieres', function (Blueprint $table) {
            // Ajouter les colonnes si elles n'existent pas déjà
            if (!Schema::hasColumn('filieres', 'specialite')) {
                $table->string('specialite')->after('id');
            }
            if (!Schema::hasColumn('filieres', 'code')) {
                $table->string('code')->nullable()->after('specialite');
            }
            if (!Schema::hasColumn('filieres', 'universite')) {
                $table->string('universite')->nullable()->after('code');
            }
            if (!Schema::hasColumn('filieres', 'type_bac')) {
                $table->string('type_bac')->nullable()->after('universite');
            }
            if (!Schema::hasColumn('filieres', 'formule')) {
                $table->string('formule')->nullable()->after('type_bac');
            }
            if (!Schema::hasColumn('filieres', 'annee')) {
                $table->integer('annee')->nullable()->after('formule');
            }
        });
    }

    public function down(): void
    {
        Schema::table('filieres', function (Blueprint $table) {
            $table->dropColumn([
                'specialite', 'code', 'universite', 
                'type_bac', 'formule', 'annee'
            ]);
        });
    }
};