<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            // Ajouter les colonnes si elles n'existent pas
            if (!Schema::hasColumn('questions', 'type')) {
                $table->string('type')->default('text')->after('question');
            }
            
            if (!Schema::hasColumn('questions', 'options')) {
                $table->json('options')->nullable()->after('type');
            }
            
            if (!Schema::hasColumn('questions', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('options');
            }
            
            if (!Schema::hasColumn('questions', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('is_active')->constrained('users');
            }
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn(['type', 'options', 'is_active', 'created_by']);
        });
    }
};