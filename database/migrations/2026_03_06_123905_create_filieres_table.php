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
    // database/migrations/xxxx_xx_xx_create_filieres_table.php

    Schema::create('filieres', function (Blueprint $table) {
        $table->id();
        $table->string('specialite');
        $table->string('code')->nullable();
        $table->string('universite')->nullable();
        $table->string('type_bac')->nullable();
        $table->string('formule')->nullable();
        $table->integer('annee')->nullable();
        $table->timestamps();
    });

}




    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('filieres');
    }
};
