<?php
// database/migrations/2026_04_04_000001_create_etudiants_profiles_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('etudiants_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('etudiant_id')->constrained('users')->onDelete('cascade');
            $table->json('interets')->nullable();
            $table->json('competences')->nullable();
            $table->json('preferences')->nullable();
            $table->timestamps();
            
            // Index pour accélérer les recherches
            $table->index('etudiant_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('etudiants_profiles');
    }
};