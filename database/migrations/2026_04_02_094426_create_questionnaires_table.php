<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('questionnaires', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conseiller_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('etudiant_id')->constrained('users')->onDelete('cascade');
            $table->string('titre')->default('Questionnaire');
            $table->enum('statut', ['envoye', 'repondu'])->default('envoye');
            $table->timestamp('envoye_le')->nullable();
            $table->timestamp('repondu_le')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('questionnaires');
    }
};