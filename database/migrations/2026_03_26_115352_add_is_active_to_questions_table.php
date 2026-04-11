<?php
// database/migrations/2024_xx_xx_add_is_active_to_questions.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            if (!Schema::hasColumn('questions', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
            if (!Schema::hasColumn('questions', 'categorie')) {
                $table->string('categorie')->nullable()->after('type');
            }
            if (!Schema::hasColumn('questions', 'mots_cles')) {
                $table->json('mots_cles')->nullable()->after('categorie');
            }
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn(['is_active', 'categorie', 'mots_cles']);
        });
    }
};