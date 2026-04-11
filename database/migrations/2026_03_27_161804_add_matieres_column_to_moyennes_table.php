<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('moyennes', function (Blueprint $table) {
            // Ajouter la colonne matieres si elle n'existe pas
            if (!Schema::hasColumn('moyennes', 'matieres')) {
                $table->json('matieres')->after('specialite');
            }
            
            // Vérifier et ajouter d'autres colonnes si nécessaire
            if (!Schema::hasColumn('moyennes', 'score_plus_7')) {
                $table->decimal('score_plus_7', 8, 2)->after('score');
            }
        });
    }

    public function down()
    {
        Schema::table('moyennes', function (Blueprint $table) {
            $table->dropColumn('matieres');
            $table->dropColumn('score_plus_7');
        });
    }
};