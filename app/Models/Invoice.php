<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'invoicess';

    protected $fillable = [
        'patient_id',
        'created_by',
        'invoice_number',
        'amount',
        'balance_due',
        'status',
        'issue_date',
        'due_date',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_due' => 'decimal:2',
        'issue_date' => 'date',
        'due_date' => 'date',
    ];

    public function patient()
    {
        return $this->belongsTo(PatientDetail::class, 'patient_id');
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function payments()
    {
        return $this->hasMany(\App\Models\InvoicePayment::class, 'invoice_id');
    }

    public function applyPaymentAmount(float $amount): float
    {
        // Ensure numeric precision consistent with casting
        $currentBalance = (float) $this->balance_due;

        $newBalance = round(max(0, $currentBalance - $amount), 2);

        // Update status based on new balance
        if ($newBalance <= 0) {
            $this->status = 'paid';
            $this->balance_due = 0.00;
        } elseif ($newBalance < (float)$this->amount) {
            $this->status = 'partially_paid';
            $this->balance_due = $newBalance;
        } else {
            // If no reduction happened, keep unpaid
            $this->status = 'unpaid';
            $this->balance_due = $newBalance;
        }

        $this->save();

        return (float) $this->balance_due;
    }

}
