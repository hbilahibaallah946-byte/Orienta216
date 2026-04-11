<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // database/migrations/2026_04_05_add_criteres_to_filieres_table.php
public function up(): void
{
    Schema::table('filieres', function (Blueprint $table) {
        if (!Schema::hasColumn('filieres', 'criteres')) {
            $table->json('criteres')->nullable()->after('annee');
        }
    });
}

public function down(): void
{
    Schema::table('filieres', function (Blueprint $table) {
        $table->dropColumn('criteres');
    });
}
};
