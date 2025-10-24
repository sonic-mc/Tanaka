<?php

namespace App\Http\Requests\Billing;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBillingRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Adjust authorization as needed (policies/roles)
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            // We require a patient_id (comes from the admitted patient select)
            'patient_id' => [
                'required',
                'integer',
                Rule::exists('patient_details', 'id'),
            ],

            // Optional: if you still post admission_id, validate it too (must be active admission)
            'admission_id' => [
                'nullable',
                'integer',
                Rule::exists('admissions', 'id')->where(function ($query) {
                    $query->where('status', 'active');
                }),
            ],

            'amount' => ['required', 'numeric', 'min:0.01'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'patient_id.required' => 'Please select a patient.',
            'patient_id.exists' => 'Selected patient is invalid.',
            'amount.required' => 'Please enter an amount.',
        ];
    }
}
