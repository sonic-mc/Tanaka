<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patient_evaluations', function (Blueprint $table) {
            // Grading system fields
            $table->enum('severity_level', ['mild', 'moderate', 'severe', 'critical'])
                ->default('mild')
                ->after('recommendations');

            $table->enum('risk_level', ['low', 'medium', 'high'])
                ->default('low')
                ->after('severity_level');

            $table->unsignedTinyInteger('priority_score')
                ->nullable()
                ->comment('1–10 scale for urgency')
                ->after('risk_level');
        });
    }

    public function down(): void
    {
        Schema::table('patient_evaluations', function (Blueprint $table) {
            $table->dropColumn(['severity_level', 'risk_level', 'priority_score']);
        });
    }
};
