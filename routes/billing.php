<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Billing\BillingController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PaymentController;

Route::middleware(['web', 'auth'])->group(function () {
    // Billing (create invoice)
    Route::get('/billing/create', [BillingController::class, 'create'])->name('billing.create');
    Route::post('/billing', [BillingController::class, 'store'])->name('billing.store');
    

    // Invoices
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');

      // NEW: Download invoice as PDF
      Route::get('/invoices/{invoice}/download', [InvoiceController::class, 'downloadPdf'])->name('invoices.download');

    // Payments for a given invoice
  
    

});
