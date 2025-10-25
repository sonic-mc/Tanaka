<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\StoreBillingRequest;
use App\Models\PatientDetail;
use App\Models\Admission;
use App\Services\BillingService;
use App\Services\EmailService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BillingController extends Controller
{
    public function __construct(
        private BillingService $billingService,
        private EmailService $emailService
    ) {
    }

    public function create()
    {
        $admissions = Admission::with('patient')
            ->where('status', 'active')
            ->orderByDesc('admission_date')
            ->get();

        return view('billing.create', compact('admissions'));
    }

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

        try {
            $patient = PatientDetail::find($invoice->patient_id);

            if (!$patient) {
                Log::warning('Invoice created but patient not found', ['invoice_id' => $invoice->id, 'patient_id' => $invoice->patient_id]);
                return redirect()
                    ->route('invoices.show', ['invoice' => $invoice->id])
                    ->with('success', 'Invoice ' . ($invoice->invoice_number ?? '') . ' created, but patient details could not be found for email dispatch.');
            }

            $nokEmail = $patient->next_of_kin_email;
            $nokName = $patient->next_of_kin_name ?: 'Next of Kin';

            if (!$nokEmail || !filter_var($nokEmail, FILTER_VALIDATE_EMAIL)) {
                Log::info('Next of kin email missing or invalid; skipping invoice email.', [
                    'patient_id' => $patient->id,
                    'invoice_id' => $invoice->id,
                    'nokEmail' => $nokEmail,
                ]);
                return redirect()
                    ->route('invoices.show', ['invoice' => $invoice->id])
                    ->with('success', 'Invoice ' . ($invoice->invoice_number ?? '') . ' created. No valid next of kin email on file; email not sent.');
            }

            // Prepare plain-text invoice details (no PDF)
            $invoice = $invoice->fresh(['patient', 'creator', 'payments']);
            $patientName = $patient->full_name ?? trim(($patient->first_name ?? '') . ' ' . ($patient->last_name ?? ''));
            $invoiceNumber = $invoice->invoice_number ?? ('#' . $invoice->id);
            $amount = number_format((float) $invoice->amount, 2);
            $dueDate = $invoice->due_date ? (string) $invoice->due_date : 'N/A';
            $notes = $invoice->notes ? (string) $invoice->notes : '—';
            $viewUrl = route('invoices.show', ['invoice' => $invoice->id]);

            $subject = 'Invoice ' . $invoiceNumber . ' for ' . ($patientName ?: 'Patient');
            $bodyLines = [
                "Dear {$nokName},",
                "",
                "Please find below the invoice details for {$patientName}.",
                "",
                "──────────────────────────────────────────────",
                "🧾 INVOICE SUMMARY",
                "──────────────────────────────────────────────",
                "Invoice Number : {$invoiceNumber}",
                "Patient Name   : {$patientName}",
                "Amount Due     : {$amount}",
                "Due Date       : {$dueDate}",
                "Notes          : " . ($notes ?: 'None'),
                "──────────────────────────────────────────────",
                "",
                "📎 Attached is a PDF copy of the invoice.",
                "",
                "You may also view the invoice online at the following link:",
                "[Insert secure invoice URL here]",
                "",
                "If you have any questions or require assistance, please contact our billing department.",
                "",
                "Thank you for your attention.",
                "",
                "Warm regards,",
                "Chimhanda Psych Hospital Billing Team"
            ];
            
            $body = implode("\n", $bodyLines);

            // Send email without attachments
            $result = $this->emailService->sendEmailWithAttachment(
                recipientEmail: $nokEmail,
                subject: $subject,
                body: $body,
                attachments: [] // No PDF attachment
            );

            if (!($result['success'] ?? false)) {
                Log::error('Failed sending invoice email', [
                    'invoice_id' => $invoice->id,
                    'patient_id' => $patient->id,
                    'result' => $result,
                ]);

                return redirect()
                    ->route('invoices.show', ['invoice' => $invoice->id])
                    ->with('success', 'Invoice ' . ($invoice->invoice_number ?? '') . ' created, but failed to send email to next of kin.')
                    ->with('warning', 'Email error: ' . ($result['error'] ?? 'Unknown'));
            }

            return redirect()
                ->route('invoices.show', ['invoice' => $invoice->id])
                ->with('success', 'Invoice ' . ($invoice->invoice_number ?? '') . ' created and emailed to next of kin successfully.');
        } catch (\Throwable $e) {
            Log::error('Exception while emailing invoice to next of kin', [
                'invoice_id' => $invoice->id ?? null,
                'patient_id' => $payload['patient_id'] ?? null,
                'message' => $e->getMessage(),
            ]);

            return redirect()
                ->route('invoices.show', ['invoice' => $invoice->id])
                ->with('success', 'Invoice ' . ($invoice->invoice_number ?? '') . ' created, but emailing to next of kin failed due to an exception.');
        }
    }
}
