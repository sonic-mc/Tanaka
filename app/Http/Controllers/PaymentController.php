<?php

namespace App\Http\Controllers;

use App\Http\Requests\Payments\StorePaymentRequest;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function create(Invoice $invoice)
    {
        abort_if($invoice->status === 'paid', 403, 'Invoice already paid.');

        return view('payments.create', compact('invoice'));
    }

    /**
     * Store a payment against an invoice (writes to invoice_payments table).
     *
     * This controller does not rely on an external BillingService; it:
     *  - creates an InvoicePayment record
     *  - updates the invoice's balance_due and status atomically
     */
    public function store(StorePaymentRequest $request, Invoice $invoice): RedirectResponse
    {
        $data = $request->validated();

        // Ensure amount is positive
        $amount = (float) $data['amount'];

        if ($amount <= 0) {
            return redirect()->back()->withInput()->withErrors(['amount' => 'Payment amount must be greater than zero.']);
        }

        $method = $data['method'] ?? 'cash';
        $transactionRef = $data['transaction_ref'] ?? null;
        $paidAt = $data['paid_at'] ?? now();

        DB::beginTransaction();

        try {
            // Create payment record
            $payment = InvoicePayment::create([
                'invoice_id' => $invoice->id,
                'patient_id' => $invoice->patient_id,
                'received_by' => Auth::id(),
                'amount' => $amount,
                'method' => $method,
                'transaction_ref' => $transactionRef,
                'paid_at' => $paidAt,
            ]);

            // Update invoice balance and status
            $newBalance = $invoice->applyPaymentAmount($amount);

            DB::commit();

            return redirect()
                ->route('invoices.show', $invoice)
                ->with('success', 'Payment of ' . number_format($payment->amount, 2) . ' recorded successfully. New balance: ' . number_format($newBalance, 2));
        } catch (\Throwable $e) {
            DB::rollBack();

            // Log or report in real app
            return redirect()->back()->withInput()->withErrors(['error' => 'Could not record payment: ' . $e->getMessage()]);
        }
    }
}
