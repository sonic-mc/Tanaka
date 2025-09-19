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
        Schema::create('progress_reports', function (Blueprint $table) {
            $table->id();
            
            // Relationships
            $table->foreignId('patient_id')->constrained('patients')->onDelete('cascade');
            $table->foreignId('reported_by')->constrained('users')->onDelete('cascade'); // nurse or psychiatrist
        
            // Symptom Severity
            $table->tinyInteger('depressed_mood')->nullable(); // 1-10 scale
            $table->tinyInteger('anxiety')->nullable();
            $table->string('hallucinations')->nullable(); // auditory/visual/other
            $table->string('delusions')->nullable(); // paranoid/grandiose/other
            $table->tinyInteger('sleep_disturbance')->nullable(); // 1-10 scale
            $table->tinyInteger('appetite_changes')->nullable(); // 1-10 scale
            $table->tinyInteger('suicidal_ideation')->nullable(); // 1-10 scale
        
            // Functional Status
            $table->string('self_care')->nullable(); // Independent / Needs support / Dependent
            $table->string('work_school')->nullable(); // Good / Fair / Poor
            $table->string('social_interactions')->nullable(); // Normal / Limited / Isolated
            $table->string('daily_activities')->nullable(); // Independent / Needs help / Dependent
        
            // Cognitive & Emotional Functioning
            $table->string('attention')->nullable();
            $table->string('memory')->nullable();
            $table->string('decision_making')->nullable();
            $table->string('emotional_regulation')->nullable();
            $table->string('insight')->nullable();
        
            // Behavioral Observations
            $table->boolean('medication_adherence')->nullable();
            $table->boolean('therapy_engagement')->nullable();
            $table->string('risk_behaviors')->nullable(); // self-harm/aggression/substance use
            $table->string('sleep_activity_patterns')->nullable();
        
            // Physical Health
            $table->decimal('weight', 5, 2)->nullable(); // in kg
            $table->string('vital_signs')->nullable();
            $table->string('medication_side_effects')->nullable();
            $table->string('general_health')->nullable();
        
            // Social Support & Environment
            $table->string('family_support')->nullable();
            $table->string('peer_support')->nullable();
            $table->string('housing_stability')->nullable();
            $table->string('access_to_services')->nullable();
        
            // Risk Assessment
            $table->tinyInteger('suicide_risk')->nullable(); // Low/Moderate/High mapped to int
            $table->tinyInteger('homicide_risk')->nullable();
            $table->tinyInteger('self_neglect_risk')->nullable();
            $table->tinyInteger('vulnerability_risk')->nullable();
        
            // Treatment Goals (JSON to allow dynamic number of goals)
            $table->json('treatment_goals')->nullable(); // [{"goal":"Attend therapy","baseline":"3","current":"5","notes":"..."}]
        
            // Notes
            $table->text('notes')->nullable();
        
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('progress_reports');
    }
};
