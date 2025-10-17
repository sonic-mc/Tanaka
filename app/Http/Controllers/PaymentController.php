<?php

namespace App\Http\Controllers;

use App\Http\Requests\Payments\StorePaymentRequest;
use App\Models\Invoice;
use App\Services\BillingService;

class PaymentController extends Controller
{
    public function __construct(private BillingService $billingService)
    {
    }

    public function create(Invoice $invoice)
    {
        abort_if($invoice->status === 'paid', 403, 'Invoice already paid.');

        return view('payments.create', compact('invoice'));
    }

    public function store(StorePaymentRequest $request, Invoice $invoice)
    {
        $payment = $this->billingService->applyPayment($invoice, [
            'amount' => $request->input('amount'),
            'method' => $request->input('method', 'cash'),
            'transaction_ref' => $request->input('transaction_ref'),
            'paid_at' => $request->input('paid_at') ?? now(),
            'received_by' => auth()->id(),
        ]);

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', 'Payment of ' . number_format($payment->amount, 2) . ' recorded successfully.');
    }
}
