<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\StoreBillingRequest;
use App\Models\PatientDetail;
use App\Models\Admission;
use App\Services\BillingService;
use Illuminate\Support\Facades\Auth;

class BillingController extends Controller
{
    public function __construct(private BillingService $billingService)
    {
    }

    /**
     * Show form to create a new invoice.
     *
     * Instead of listing all patients, we list only currently admitted patients (admissions.status = 'active').
     * Each select option contains the admission id and a data attribute with the underlying patient id.
     * A hidden patient_id input will be synced by JS so the existing StoreBillingRequest (which expects patient_id)
     * continues to work without changing the service contract.
     */
    public function create()
    {
        $admissions = Admission::with('patient')
            ->where('status', 'active')
            ->orderByDesc('admission_date')
            ->get();

        return view('billing.create', compact('admissions'));
    }

    /**
     * Store a new invoice.
     *
     * The StoreBillingRequest is expected to validate 'patient_id' as the target patient.
     * The form will supply a hidden patient_id that maps from the selected admission.
     */
    public function store(StoreBillingRequest $request)
    {
        $payload = [
            'patient_id' => $request->integer('patient_id'),
            'amount' => $request->input('amount'),
            'due_date' => $request->input('due_date') ?: null,
            'notes' => $request->input('notes'),
            'created_by' => Auth::id(),
        ];

        $invoice = $this->billingService->createInvoice($payload);

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', 'Invoice ' . ($invoice->invoice_number ?? '') . ' created successfully.');
    }
}
