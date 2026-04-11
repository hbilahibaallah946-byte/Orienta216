// database/migrations/xxxx_drop_questions_reponses_tables.php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('reponses');
        Schema::dropIfExists('questions');
    }
    public function down(): void {}
};