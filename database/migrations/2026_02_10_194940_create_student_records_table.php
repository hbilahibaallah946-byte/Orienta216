<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_records', function (Blueprint $table) {
            $table->id();

            // relation avec utilisateur
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // informations générales
            $table->string('section');   // math, sc, lettres...
            $table->string('session');   // principale ou controle

            // matières
            $table->float('math')->nullable();
            $table->float('physics')->nullable();
            $table->float('science')->nullable();
            $table->float('arabic')->nullable();
            $table->float('french')->nullable();
            $table->float('english')->nullable();
            $table->float('philosophy')->nullable();
            $table->float('info')->nullable();
            $table->float('option')->nullable();
            $table->boolean('sport')->default(true);

            // résultats calculés
            $table->float('average')->nullable();
            $table->float('score')->nullable();
            $table->float('score_boosted')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_records');
    }
};
