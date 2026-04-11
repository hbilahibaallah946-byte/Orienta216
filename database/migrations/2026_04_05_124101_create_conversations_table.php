<?php
// database/migrations/2026_04_05_000001_create_conversations_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('etudiant_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('conseiller_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('conseiller_nom')->nullable();
            $table->enum('statut', ['en_attente', 'pris_en_charge', 'ferme'])->default('en_attente');
            $table->timestamp('pris_le')->nullable();
            $table->timestamp('dernier_message_at')->nullable();
            $table->timestamps();
            
            $table->index('statut');
            $table->index('etudiant_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('conversations');
    }
};