<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            // Rendre la colonne categorie nullable
            $table->string('categorie')->nullable()->change();
            
            // Ajouter la colonne mots_cles si elle n'existe pas
            if (!Schema::hasColumn('questions', 'mots_cles')) {
                $table->json('mots_cles')->nullable()->after('categorie');
            }
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            // Revenir en arrière (cette opération peut échouer si des NULL existent)
            $table->string('categorie')->nullable(false)->change();
            
            if (Schema::hasColumn('questions', 'mots_cles')) {
                $table->dropColumn('mots_cles');
            }
        });
    }
};