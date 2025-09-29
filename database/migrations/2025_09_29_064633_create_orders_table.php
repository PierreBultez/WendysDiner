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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            // Using decimal for money is the best practice to avoid floating point issues.
            $table->decimal('total_amount', 8, 2);
            $table->string('status')->default('en cours'); // ex: en cours, terminée
            $table->text('notes')->nullable(); // General comments about the order
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
