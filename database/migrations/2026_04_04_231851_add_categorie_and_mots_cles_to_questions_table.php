<?php
// database/migrations/2026_04_05_000001_add_categorie_and_mots_cles_to_questions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('questions', function (Blueprint $table) {
            // Ajouter la colonne categorie (après options)
            if (!Schema::hasColumn('questions', 'categorie')) {
                $table->string('categorie')->default('interet')->after('options');
            }
            
            // Ajouter la colonne mots_cles (après categorie)
            if (!Schema::hasColumn('questions', 'mots_cles')) {
                $table->json('mots_cles')->nullable()->after('categorie');
            }
        });
    }

    public function down()
    {
        Schema::table('questions', function (Blueprint $table) {
            if (Schema::hasColumn('questions', 'categorie')) {
                $table->dropColumn('categorie');
            }
            if (Schema::hasColumn('questions', 'mots_cles')) {
                $table->dropColumn('mots_cles');
            }
        });
    }
};