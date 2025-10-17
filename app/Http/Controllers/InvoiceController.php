<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;


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

     // NEW: Download PDF
     public function downloadPdf(Invoice $invoice)
     {
         $invoice->load([
             'patient:id,first_name,last_name,patient_code',
             'payments' => fn ($q) => $q->latest(),
         ]);
 
         $pdf = Pdf::loadView('invoices.pdf', [
             'invoice' => $invoice,
             'appName' => config('app.name'),
         ])->setPaper('a4');
 
         $filename = 'Invoice-' . $invoice->invoice_number . '.pdf';
 
         return $pdf->download($filename);
         // For inline preview in browser use:
         // return $pdf->stream($filename);
     }
 
}
