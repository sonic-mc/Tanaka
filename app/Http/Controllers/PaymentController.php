<?php

namespace App\Http\Controllers;

use App\Http\Requests\Payments\StorePaymentRequest;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\PatientDetail;
use App\Services\EmailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function __construct(
        private EmailService $emailService
    ) {
    }

    public function create(Invoice $invoice)
    {
        abort_if($invoice->status === 'paid', 403, 'Invoice already paid.');

        return view('payments.create', compact('invoice'));
    }

    /**
     * Store a payment against an invoice (writes to invoice_payments table).
     *
     * Auto-generates a unique transaction reference and emails payment details
     * to the patient's next of kin (no attachments).
     */
    public function store(StorePaymentRequest $request, Invoice $invoice): RedirectResponse
    {
        $data = $request->validated();

        // Ensure amount is positive
        $amount = (float) ($data['amount'] ?? 0);

        if ($amount <= 0) {
            return redirect()->back()->withInput()->withErrors(['amount' => 'Payment amount must be greater than zero.']);
        }

        $method = $data['method'] ?? 'cash';
        $paidAt = $data['paid_at'] ?? now();

        DB::beginTransaction();

        try {
            // Generate unique transaction reference
            $transactionRef = $this->generateTransactionRef();

            // Create payment record
            $payment = InvoicePayment::create([
                'invoice_id' => $invoice->id,
                'patient_id' => $invoice->patient_id,
                'received_by' => Auth::id(),
                'amount' => $amount,
                'method' => $method,
                'transaction_ref' => $transactionRef,
                'paid_at' => $paidAt,
            ]);

            // Update invoice balance and status
            $newBalance = $invoice->applyPaymentAmount($amount);

            DB::commit();

            // Email payment details to patient's next of kin (no PDF)
            try {
                $patient = PatientDetail::find($invoice->patient_id);

                if ($patient) {
                    $nokEmail = $patient->next_of_kin_email;
                    $nokName = $patient->next_of_kin_name ?: 'Next of Kin';

                    if ($nokEmail && filter_var($nokEmail, FILTER_VALIDATE_EMAIL)) {
                        $patientName = $patient->full_name ?? trim(($patient->first_name ?? '') . ' ' . ($patient->last_name ?? ''));
                        $invoiceNumber = $invoice->invoice_number ?? ('#' . $invoice->id);
                        $viewUrl = route('invoices.show', $invoice);

                        $subject = 'Payment received for Invoice ' . $invoiceNumber . ' (' . $patientName . ')';
                        $bodyLines = [
                            "Dear {$nokName},",
                            "",
                            "We wish to inform you that a payment has been successfully recorded for {$patientName}.",
                            "",
                            "──────────────────────────────────────────────",
                            "💳 PAYMENT RECEIPT",
                            "──────────────────────────────────────────────",
                            "Invoice Number     : {$invoiceNumber}",
                            "Transaction Ref    : {$transactionRef}",
                            "Amount Paid        : " . number_format($payment->amount, 2),
                            "Payment Method     : " . ucwords(str_replace('_', ' ', $method)),
                            "Date of Payment    : " . (is_string($paidAt) ? $paidAt : $paidAt->format('Y-m-d H:i:s')),
                            "Remaining Balance  : " . number_format((float) $newBalance, 2),
                            "──────────────────────────────────────────────",
                            "",
        
                            "",
                            "If you have any questions or need assistance, please contact our billing department.",
                            "",
                            "Thank you for your continued trust.",
                            "",
                            "Warm regards,",
                            "Chimhanda Hospital Billing Team"
                        ];
                        
                        $body = implode("\n", $bodyLines);

                        $result = $this->emailService->sendEmailWithAttachment(
                            recipientEmail: $nokEmail,
                            subject: $subject,
                            body: $body,
                            attachments: [] // No attachment
                        );

                        if (!($result['success'] ?? false)) {
                            Log::error('Failed sending payment email to next of kin', [
                                'invoice_id' => $invoice->id,
                                'patient_id' => $patient->id,
                                'result' => $result,
                            ]);
                        }
                    } else {
                        Log::info('Next of kin email missing or invalid; skipping payment email.', [
                            'patient_id' => $patient->id,
                            'invoice_id' => $invoice->id,
                            'nokEmail' => $nokEmail,
                        ]);
                    }
                } else {
                    Log::warning('Payment recorded but patient not found for emailing NOK', [
                        'invoice_id' => $invoice->id,
                        'patient_id' => $invoice->patient_id,
                    ]);
                }
            } catch (\Throwable $mailEx) {
                Log::error('Exception while emailing payment details to next of kin', [
                    'invoice_id' => $invoice->id,
                    'patient_id' => $invoice->patient_id,
                    'message' => $mailEx->getMessage(),
                ]);
            }

            return redirect()
                ->route('invoices.show', $invoice)
                ->with('success', 'Payment of ' . number_format($payment->amount, 2) . ' recorded successfully. Transaction Ref: ' . $transactionRef . '. New balance: ' . number_format($newBalance, 2));
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()->back()->withInput()->withErrors(['error' => 'Could not record payment: ' . $e->getMessage()]);
        }
    }

    protected function generateTransactionRef(): string
    {
        do {
            // Example: TRX-20251025-000000-AB12CD
            $ref = 'TRX-' . now()->format('Ymd-His') . '-' . strtoupper(Str::random(6));
        } while (InvoicePayment::where('transaction_ref', $ref)->exists());

        return $ref;
    }
}
