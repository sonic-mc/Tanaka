<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_progress_reports', function (Blueprint $table) {
            $table->id();

            // Relationships
            $table->foreignId('patient_id')->constrained('patient_details')->onDelete('cascade');
            $table->foreignId('admission_id')->nullable()->constrained('admissions')->nullOnDelete();
            $table->foreignId('evaluation_id')->nullable()->constrained('patient_evaluations')->nullOnDelete();
            $table->foreignId('clinician_id')->nullable()->constrained('users')->nullOnDelete();

            // When the report was made (useful for trend x-axis)
            $table->date('report_date')->nullable();

            // Common standardized psychiatric / outcome scales (nullable — record whichever are collected)
            // Numeric ranges chosen generous to accommodate different scales; you may narrow later.
            $table->decimal('gaf_score', 5, 2)->nullable()->comment('Global Assessment of Functioning');
            $table->tinyInteger('phq9_score')->nullable()->comment('PHQ-9 depression total (0-27)');
            $table->tinyInteger('gad7_score')->nullable()->comment('GAD-7 anxiety total (0-21)');
            $table->decimal('who_das_score', 6, 2)->nullable()->comment('WHO-DAS functional score');
            $table->decimal('honos_score', 6, 2)->nullable()->comment('HoNOS total score');
            $table->decimal('bprs_score', 6, 2)->nullable()->comment('BPRS total score');
            $table->tinyInteger('cgi_severity')->nullable()->comment('CGI-S (1-7)');

            // A single overall / clinician global severity or wellness measure
            $table->decimal('global_severity_score', 5, 2)->nullable()->comment('Site-specific normalized severity/wellness score');

            // Functional / social measures (optional, for trend comparisons)
            $table->decimal('functional_score', 6, 2)->nullable()->comment('Custom functional ability score');

            // Risk / safety assessment summary
            $table->enum('risk_level', ['none', 'low', 'moderate', 'high', 'critical'])->default('none');
            $table->text('risk_assessment')->nullable();

            // Free-text narrative sections
            $table->text('symptom_summary')->nullable();
            $table->text('observations')->nullable();
            $table->text('treatment_plan')->nullable();
            $table->text('medication_changes')->nullable();

            // Flexible structured metrics (JSON) — store questionnaire item-level answers, tags, scoring breakdowns
            $table->json('metrics')->nullable()->comment('Structured per-scale/questionnaire details (JSON)');

            // Attachments (array of file paths/urls), or keep null if not used
            $table->json('attachments')->nullable();

            // Audit
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('last_modified_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indexes to support trend queries and lookups
            $table->index(['patient_id', 'report_date']);
            $table->index('report_date');
            $table->index('clinician_id');
            $table->index('risk_level');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_progress_reports');
    }
};
