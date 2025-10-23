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
        Schema::create('admissions', function (Blueprint $table) {
            $table->id();

            // Relationships
            $table->foreignId('patient_id')->constrained('patient_details')->onDelete('cascade');
            $table->foreignId('evaluation_id')->nullable()->constrained('patient_evaluations')->nullOnDelete();
            $table->foreignId('admitted_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('assigned_psychiatrist_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('care_level_id')->nullable()->constrained('care_levels')->nullOnDelete();

            // Admission details
            $table->date('admission_date');
            $table->text('admission_reason')->nullable();
            $table->string('room_number')->nullable();
            $table->enum('status', ['active', 'discharged', 'transferred', 'deceased'])->default('active');

            // Audit
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('last_modified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admissions');
    }
};
