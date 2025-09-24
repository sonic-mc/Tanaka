<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Traits\AuditLogger;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;


class BillingStatementController extends Controller
{
    use AuditLogger;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $invoices = Invoice::with('patient')
            ->when($request->filled('patient_id'), fn($q) => $q->where('patient_id', $request->patient_id))
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->orderByDesc('issue_date')
            ->paginate(50);

         $patients = Patient::orderBy('last_name')->get();

        return view('admin.billings.index', compact('invoices', 'patients'));
    }

    public function show(Invoice $invoice)
    {
        $invoice->load('patient', 'payments');

        return view('admin.billings.show', compact('invoice'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
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
