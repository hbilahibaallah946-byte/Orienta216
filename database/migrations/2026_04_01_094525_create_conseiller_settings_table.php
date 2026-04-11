<?php
// database/migrations/xxxx_create_conseiller_settings_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // database/migrations/2026_04_05_create_conseiller_settings_table.php
public function up(): void
{
    Schema::create('conseiller_settings', function (Blueprint $table) {
        $table->id();
        $table->foreignId('conseiller_id')->constrained('users')->onDelete('cascade');
        $table->json('jours_disponibles')->nullable();
        $table->string('heure_debut', 5)->nullable();
        $table->string('heure_fin', 5)->nullable();
        $table->integer('max_etudiants_par_jour')->default(10);
        $table->boolean('notif_nouveau_message')->default(true);
        $table->boolean('notif_nouveau_etudiant')->default(true);
        $table->boolean('notif_email')->default(false);
        $table->timestamps();
    });
}

    public function down(): void
    {
        Schema::dropIfExists('conseiller_settings');
    }
};