<?php
// database/migrations/xxxx_fix_reponses_table.php
// ⚠️  CORRIGE l'incohérence etudiant_id → user_id
// Lance : php artisan migrate

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Si la colonne s'appelle encore etudiant_id, on la renomme
        if (Schema::hasColumn('reponses', 'etudiant_id')
            && !Schema::hasColumn('reponses', 'user_id')) {

            Schema::table('reponses', function (Blueprint $table) {
                // Supprimer la contrainte unique avant renommage
                $table->dropUnique(['etudiant_id', 'question_id']);
                $table->renameColumn('etudiant_id', 'user_id');
            });

            Schema::table('reponses', function (Blueprint $table) {
                // Recréer la contrainte unique avec le bon nom
                $table->unique(['user_id', 'question_id']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('reponses', 'user_id')
            && !Schema::hasColumn('reponses', 'user_id')) {

            Schema::table('reponses', function (Blueprint $table) {
                $table->dropUnique(['user_id', 'question_id']);
                $table->renameColumn('user_id', 'user_id');
            });

            Schema::table('reponses', function (Blueprint $table) {
                $table->unique(['user_id', 'question_id']);
            });
        }
    }
};