<?php

namespace App\Services;

use App\Models\BillingStatement;
use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BillingService
{
    /**
     * Create an invoice and update the patient's billing statement.
     */
    public function createInvoice(array $data): Invoice
    {
        return DB::transaction(function () use ($data) {
            $invoice = new Invoice();
            $invoice->patient_id = $data['patient_id'];
            $invoice->created_by = $data['created_by'] ?? null;
            $invoice->invoice_number = $this->generateUniqueInvoiceNumber();
            $invoice->amount = $data['amount'];
            $invoice->balance_due = $data['amount']; // initially all due
            $invoice->status = 'unpaid';
            $invoice->issue_date = $data['issue_date'] ?? Carbon::today();
            $invoice->due_date = $data['due_date'] ?? null;
            $invoice->notes = $data['notes'] ?? null;
            $invoice->save();

            $this->upsertBillingStatementOnInvoiceCreate($invoice);

            return $invoice;
        });
    }

    /**
     * Apply a payment to an invoice and update billing statement.
     */
    public function applyPayment(Invoice $invoice, array $data): Payment
    {
        return DB::transaction(function () use ($invoice, $data) {
            $amount = (float) $data['amount'];
            $currentBalance = (float) $invoice->balance_due;

            if ($amount <= 0) {
                throw ValidationException::withMessages([
                    'amount' => 'Payment amount must be greater than 0.',
                ]);
            }
            if ($amount > $currentBalance) {
                throw ValidationException::withMessages([
                    'amount' => 'Payment amount cannot exceed the outstanding balance of ' . number_format($currentBalance, 2),
                ]);
            }

            $payment = new Payment();
            $payment->invoice_id = $invoice->id;
            $payment->patient_id = $invoice->patient_id; // derive from invoice
            $payment->received_by = $data['received_by'] ?? null;
            $payment->amount = $amount;
            $payment->method = $data['method'] ?? 'cash';
            $payment->transaction_ref = $data['transaction_ref'] ?? null;
            $payment->paid_at = $data['paid_at'] ?? now();
            $payment->save();

            // Update invoice balance and status
            $invoice->balance_due = $currentBalance - $amount;
            if ($invoice->balance_due <= 0.0) {
                $invoice->balance_due = 0.00;
                $invoice->status = 'paid';
            } elseif ($invoice->balance_due < $invoice->amount) {
                $invoice->status = 'partially_paid';
            } else {
                $invoice->status = 'unpaid';
            }
            $invoice->save();

            // Update billing statement
            $this->updateBillingStatementOnPayment($invoice->patient_id, $amount);

            return $payment;
        });
    }

    protected function generateUniqueInvoiceNumber(): string
    {
        $prefix = 'INV-' . now()->format('Ymd') . '-';
        do {
            $candidate = $prefix . strtoupper(Str::random(6));
        } while (Invoice::where('invoice_number', $candidate)->exists());

        return $candidate;
    }

    protected function upsertBillingStatementOnInvoiceCreate(Invoice $invoice): void
    {
        $bs = BillingStatement::firstOrNew(['patient_id' => $invoice->patient_id]);
        $bs->total_amount = (float) ($bs->total_amount ?? 0) + (float) $invoice->amount;
        $bs->outstanding_balance = (float) ($bs->outstanding_balance ?? 0) + (float) $invoice->balance_due;
        $bs->last_updated = now();
        $bs->save();
    }

    protected function updateBillingStatementOnPayment(int $patientId, float $paymentAmount): void
    {
        $bs = BillingStatement::firstOrNew(['patient_id' => $patientId]);
        $bs->total_amount = (float) ($bs->total_amount ?? 0); // unchanged on payment
        $bs->outstanding_balance = max(0, (float) ($bs->outstanding_balance ?? 0) - $paymentAmount);
        $bs->last_updated = now();
        $bs->save();
    }
}
