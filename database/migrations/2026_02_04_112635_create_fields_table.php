<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::create('fields', function (Blueprint $table) {
        $table->id();
        $table->string('name', 150);

        $table->foreignId('university_id')->constrained('universities')->onDelete('cascade');

        $table->string('bac_type_required', 50);
        $table->float('min_score');

        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fields');
    }
};
