<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('moyennes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('specialite');
            $table->json('matieres');
            $table->decimal('moyenne', 5, 2);
            $table->decimal('score', 6, 2);
            $table->decimal('score_plus_7', 6, 2);
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index('user_id');
            $table->index('created_at');
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('moyennes');
    }
};