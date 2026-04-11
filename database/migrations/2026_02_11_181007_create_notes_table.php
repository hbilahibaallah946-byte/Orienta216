<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('notes', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->foreignId('specialite_id')->constrained()->cascadeOnDelete();

        $table->float('math')->nullable();
        $table->float('physique')->nullable();
        $table->float('science')->nullable();
        $table->float('arabe')->nullable();
        $table->float('francais')->nullable();
        $table->float('anglais')->nullable();
        $table->float('philosophie')->nullable();
        $table->float('informatique')->nullable();
        $table->float('option')->nullable();
        $table->float('sport')->nullable();

        $table->float('moyenne')->nullable();

        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};
