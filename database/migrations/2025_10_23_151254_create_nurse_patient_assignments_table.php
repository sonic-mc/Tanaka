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
        Schema::create('nurse_patient_assignments', function (Blueprint $table) {
            $table->id();
        
            // Relationships
            $table->foreignId('nurse_id')->constrained('users')->onDelete('cascade'); // assuming nurses are in users table
            $table->foreignId('admission_id')->constrained('admissions')->onDelete('cascade');
        
            // Optional metadata
            $table->string('shift')->nullable(); // e.g. morning, evening, night
            $table->date('assigned_date')->nullable();
            $table->text('notes')->nullable();
        
            // Audit
            $table->foreignId('assigned_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nurse_patient_assignments');
    }
};
