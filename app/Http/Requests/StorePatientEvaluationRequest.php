<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\PatientEvaluation;

class StorePatientEvaluationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'integer', 'exists:patient_details,id'],
            'evaluation_date' => ['required', 'date'],
            'evaluation_type' => ['required', 'in:' . implode(',', [
                PatientEvaluation::TYPE_INITIAL,
                PatientEvaluation::TYPE_FOLLOW_UP,
                PatientEvaluation::TYPE_EMERGENCY,
            ])],
            'presenting_complaints' => ['nullable', 'string'],
            'clinical_observations' => ['nullable', 'string'],
            'diagnosis' => ['nullable', 'string'],
            'recommendations' => ['nullable', 'string'],
            'decision' => ['required', 'in:' . implode(',', [
                PatientEvaluation::DECISION_ADMIT,
                PatientEvaluation::DECISION_OUTPATIENT,
                PatientEvaluation::DECISION_REFER,
                PatientEvaluation::DECISION_MONITOR,
            ])],
            'requires_admission' => ['nullable', 'boolean'],
            'admission_trigger_notes' => ['nullable', 'string'],
            'severity_level' => ['required', 'in:' . implode(',', [
                PatientEvaluation::SEVERITY_MILD,
                PatientEvaluation::SEVERITY_MODERATE,
                PatientEvaluation::SEVERITY_SEVERE,
                PatientEvaluation::SEVERITY_CRITICAL,
            ])],
            'risk_level' => ['required', 'in:' . implode(',', [
                PatientEvaluation::RISK_LOW,
                PatientEvaluation::RISK_MEDIUM,
                PatientEvaluation::RISK_HIGH,
            ])],
            'priority_score' => ['nullable', 'integer', 'min:1', 'max:10'],
        ];
    }
}