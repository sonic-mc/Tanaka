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
        Schema::create('patient_evaluations', function (Blueprint $table) {
            $table->id();

            // Relationships
            $table->foreignId('patient_id')->constrained('patient_details')->onDelete('cascade');
            $table->foreignId('psychiatrist_id')->constrained('users')->onDelete('cascade');

            // Evaluation metadata
            $table->date('evaluation_date');
            $table->enum('evaluation_type', ['initial', 'follow-up', 'emergency'])->default('initial');
            $table->text('presenting_complaints')->nullable();
            $table->text('clinical_observations')->nullable();
            $table->text('diagnosis')->nullable();
            $table->text('recommendations')->nullable();

            // Severity & Risk
            $table->enum('severity_level', ['mild', 'moderate', 'severe', 'critical'])->default('mild');
            $table->enum('risk_level', ['low', 'medium', 'high'])->default('low');
            $table->unsignedTinyInteger('priority_score')->nullable()->comment('1–10 scale for urgency');

            // Decision logic
            $table->enum('decision', ['admit', 'outpatient', 'refer', 'monitor'])->default('outpatient');
            $table->boolean('requires_admission')->default(false);
            $table->text('admission_trigger_notes')->nullable();
            $table->timestamp('decision_made_at')->nullable();

            // Administrative
            $table->unsignedBigInteger('created_by')->nullable();
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
        Schema::dropIfExists('patient_evaluations');
    }
};
