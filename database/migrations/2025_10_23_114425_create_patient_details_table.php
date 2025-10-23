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
        Schema::create('patient_details', function (Blueprint $table) {
            $table->id();

            // Identification
            $table->string('patient_code')->unique();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->enum('gender', ['male', 'female', 'other']);
            $table->date('dob')->nullable();
            $table->string('national_id_number')->nullable()->unique();
            $table->string('passport_number')->nullable()->unique();
            $table->string('photo')->nullable(); // optional profile image

            // Contact & Demographics
            $table->string('email')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('residential_address')->nullable();
            $table->string('race')->nullable();
            $table->string('religion')->nullable();
            $table->string('language')->nullable();
            $table->string('denomination')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('occupation')->nullable();


            // Medical Info
            $table->string('blood_group')->nullable();
            $table->string('allergies')->nullable();
            $table->string('disabilities')->nullable();
            $table->string('special_diet')->nullable();
            $table->string('medical_aid_provider')->nullable();
            $table->string('medical_aid_number')->nullable();
            $table->text('special_medical_requirements')->nullable();
            $table->text('current_medications')->nullable();
            $table->text('past_medical_history')->nullable();

            // Next of Kin
            $table->string('next_of_kin_name')->nullable();
            $table->string('next_of_kin_relationship')->nullable();
            $table->string('next_of_kin_contact_number')->nullable();
            $table->string('next_of_kin_email')->nullable();
            $table->string('next_of_kin_address')->nullable();

            // Administrative
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('last_modified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('patient_details');
    }
};
