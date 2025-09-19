<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('therapy_sessions', function (Blueprint $table) {
            $table->id();

            // Relationships
            $table->foreignId('patient_id')->constrained()->onDelete('cascade');
            $table->foreignId('clinician_id')->constrained('users')->onDelete('cascade');

            // Session info
            $table->dateTime('session_start');
            $table->dateTime('session_end')->nullable();
            $table->string('session_type'); // individual/group/family
            $table->string('mode'); // in-person/online
            $table->integer('session_number')->nullable();

            // Clinical content
            $table->text('presenting_issues')->nullable();
            $table->text('mental_status_exam')->nullable();
            $table->text('interventions')->nullable();
            $table->text('observations')->nullable();
            $table->text('plan')->nullable();
            $table->json('goals_progress')->nullable();

            // Administrative
            $table->string('status')->default('Scheduled'); // Scheduled / Completed / Canceled

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('therapy_sessions');
    }
};
