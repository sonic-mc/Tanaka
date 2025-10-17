<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\StoreBillingRequest;
use App\Models\Patient;
use App\Services\BillingService;

class BillingController extends Controller
{
    public function __construct(private BillingService $billingService)
    {
    }

    public function create()
    {
        $patients = Patient::query()
            ->select('id', 'first_name', 'last_name', 'patient_code')
            ->orderBy('first_name')
            ->get();

        return view('billing.create', compact('patients'));
    }

    public function store(StoreBillingRequest $request)
    {
        $invoice = $this->billingService->createInvoice([
            'patient_id' => $request->integer('patient_id'),
            'amount' => $request->input('amount'),
            'due_date' => $request->date('due_date'),
            'notes' => $request->input('notes'),
            'created_by' => auth()->id(),
        ]);

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', 'Invoice ' . $invoice->invoice_number . ' created successfully.');
    }
}
