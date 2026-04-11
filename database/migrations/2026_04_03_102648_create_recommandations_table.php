<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('recommandations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('etudiant_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('filiere_id')->constrained()->onDelete('cascade');
            $table->integer('score')->default(0);
            $table->integer('rang')->default(1);
            $table->timestamps();
            $table->unique(['etudiant_id', 'rang']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('recommandations');
    }
};