<?php

namespace App\Http\Requests\Payments;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', 'in:cash,card,mobile_money,bank_transfer'],
            'transaction_ref' => ['nullable', 'string', 'max:255'],
            'paid_at' => ['nullable', 'date'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $invoice = $this->route('invoice');
            if ($invoice) {
                $amount = (float) $this->input('amount', 0);
                $balance = (float) $invoice->balance_due;
                if ($amount > $balance) {
                    $validator->errors()->add('amount', 'Payment amount cannot exceed the outstanding balance of ' . number_format($balance, 2));
                }
                if ($invoice->status === 'paid') {
                    $validator->errors()->add('amount', 'This invoice is already fully paid.');
                }
            }
        });
    }
}
