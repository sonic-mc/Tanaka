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
     public function downloadPdf($id)
     {
         // Safe eager-load; do not attempt to eager load relationships that may not exist.
         try {
             $invoice = Invoice::with('patient')->findOrFail($id);
         } catch (ModelNotFoundException $e) {
             abort(404, 'Invoice not found.');
         }
 
         // Prefer a stored pdf_path attribute if you have one (add pdf_path to $fillable and DB when ready)
         $pdfPath = $invoice->pdf_path ?? null;
 
         if ($pdfPath) {
             // If pdf_path is stored as "invoices/filename.pdf" and stored on 'public' disk:
             $disk = Storage::disk('public');
             if ($disk->exists($pdfPath)) {
                 $fullPath = $disk->path($pdfPath);
                 return response()->download($fullPath, basename($fullPath), [
                     'Content-Type' => 'application/pdf',
                 ]);
             }
         }
 
         // Fallback to the conventional filename
         $filename = 'invoice_' . $invoice->invoice_number . '.pdf';
         $fullPath = storage_path('app/public/invoices/' . $filename);
 
         if (!file_exists($fullPath)) {
             // Helpful error to the user/admin — file missing
             abort(404, 'Invoice PDF not found on server.');
         }
 
         return response()->download($fullPath, $filename, [
             'Content-Type' => 'application/pdf',
         ]);
     }
 
}
