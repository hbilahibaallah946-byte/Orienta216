<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Ajouter la colonne status
            if (!Schema::hasColumn('users', 'status')) {
                $table->string('status')->default('pending')->after('role');
            }
            
            // Ajouter la colonne validated_by
            if (!Schema::hasColumn('users', 'validated_by')) {
                $table->unsignedBigInteger('validated_by')->nullable()->after('status');
            }
            
            // Ajouter la colonne validated_at
            if (!Schema::hasColumn('users', 'validated_at')) {
                $table->timestamp('validated_at')->nullable()->after('validated_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['status', 'validated_by', 'validated_at']);
        });
    }
};