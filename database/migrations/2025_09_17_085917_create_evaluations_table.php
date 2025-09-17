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
            
            Schema::create('evaluations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('evaluated_by')->constrained('users')->onDelete('cascade'); // doctor or nurse
            $table->text('notes')->nullable();
            $table->string('risk_level')->nullable(); // mild, moderate, severe
            $table->json('scores')->nullable(); // if you want to store structured data
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
