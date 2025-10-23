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
        Schema::create('consultation_fees', function (Blueprint $table) {
            $table->id();
            $table->enum('age_group', ['child', 'adult']); // Fee varies by age group
            $table->decimal('fee_amount', 10, 2); // e.g., 150.00
            $table->text('description')->nullable(); // Optional notes
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultation_fees');
    }
};
