<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }

    public function down(): void {
        // This allows us to roll back the migration if needed
        Schema::table('categories', function (Blueprint $table) {
            $table->text('description')->nullable()->after('slug');
        });
    }
};
