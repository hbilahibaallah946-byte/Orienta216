<?php

// database/migrations/2026_02_24_000000_create_student_profiles_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('specialite')->nullable();
            $table->json('matieres')->nullable(); // stocke les notes {"Math": 18, "Physique": 15, ...}
            $table->string('session')->default('principale'); // principale ou controle
            $table->timestamps();
        });
    }

    public function down() {
        Schema::dropIfExists('student_profiles');
    }
};
