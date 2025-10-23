<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePatientEvaluationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'exists:patient_details,id'],
            'evaluation_date' => ['required', 'date'],
            'evaluation_type' => ['required', Rule::in(['initial', 'follow-up', 'emergency'])],
            'presenting_complaints' => ['nullable', 'string'],
            'clinical_observations' => ['nullable', 'string'],
            'diagnosis' => ['nullable', 'string'],
            'recommendations' => ['nullable', 'string'],
            'decision' => ['required', Rule::in(['admit', 'outpatient', 'refer', 'monitor'])],
            'requires_admission' => ['required', 'boolean'],
            'admission_trigger_notes' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'patient_id.required' => 'Please select a patient.',
            'evaluation_date.required' => 'Evaluation date is required.',
        ];
    }
}
