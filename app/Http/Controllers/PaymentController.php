<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function store(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'method' => 'required|in:cash,card,mobile_money,bank_transfer',
            'transaction_ref' => 'nullable|string|max:255',
        ]);

        $payment = Payment::create([
            'invoice_id'      => $invoice->id,
            'patient_id'      => $invoice->patient_id,
            'received_by'     => auth()->id(),
            'amount'          => $validated['amount'],
            'method'          => $validated['method'],
            'transaction_ref' => $validated['transaction_ref'],
            'paid_at'         => now(),
        ]);

        // Update invoice balance
        $invoice->balance_due -= $payment->amount;
        $invoice->status = $invoice->balance_due <= 0 ? 'paid' : 'partially_paid';
        $invoice->save();

        // Audit log
        AuditLog::log(
            'Recorded payment',
            "Invoice #{$invoice->invoice_number}, Amount: {$payment->amount}, Method: {$payment->method}",
            'billing',
            'info'
        );

        return redirect()->route('admin.billing.show', $invoice)->with('success', 'Payment recorded successfully.');
    }



    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
