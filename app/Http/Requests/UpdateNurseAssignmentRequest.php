<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateNurseAssignmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'nurse_id' => [
                'required',
                Rule::exists('users', 'id')->where('role', 'nurse'),
            ],
            'admission_id' => ['required', Rule::exists('admissions', 'id')->where('status', 'active')],
            'shift' => ['nullable', Rule::in(['morning', 'evening', 'night'])],
            'assigned_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
