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
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
        
            // Relationships
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('staff_id')->constrained('users')->onDelete('cascade'); // doctor/psychiatrist
            $table->foreignId('medication_id')->nullable()->constrained()->onDelete('set null');
        
            // Core prescription details
            $table->string('dosage');       // e.g. "10mg"
            $table->string('frequency');    // e.g. "2x daily"
            $table->integer('duration');    // e.g. 7 (days)
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
        
            // Status lifecycle
            $table->enum('status', ['active', 'completed', 'cancelled', 'expired'])->default('active');
        
            // Optional notes
            $table->text('instructions')->nullable();
        
            $table->timestamps();
            $table->softDeletes();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};
