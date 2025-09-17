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
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('patient_code')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->enum('gender', ['male', 'female', 'other']);
            $table->date('dob')->nullable();
            $table->string('contact_number')->nullable();
            $table->date('admission_date');
            $table->text('admission_reason')->nullable();
            $table->foreignId('admitted_by')->constrained('users')->onDelete('cascade');
            $table->string('room_number')->nullable();
            $table->enum('status', ['active', 'discharged'])->default('active');
            $table->foreignId('current_care_level_id')->nullable()->constrained('care_levels')->nullOnDelete();
            $table->timestamps();
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
