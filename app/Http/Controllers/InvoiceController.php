<?php

namespace App\Http\Controllers;

use App\Models\Invoice;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::query()
            ->with(['patient:id,first_name,last_name,patient_code'])
            ->latest()
            ->paginate(15);

        return view('invoices.index', compact('invoices'));
    }

    public function show(Invoice $invoice)
    {
        $invoice->load([
            'patient:id,first_name,last_name,patient_code',
            'payments' => fn ($q) => $q->latest(),
        ]);

        return view('invoices.show', compact('invoice'));
    }
}
