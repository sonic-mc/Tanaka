<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class BillingStatement extends Model
{
    use HasFactory;

    // Matches your migration name
    protected $table = 'billings_statements';

    protected $fillable = [
        'patient_id',
        'total_amount',
        'outstanding_balance',
        'last_updated',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'outstanding_balance' => 'decimal:2',
        'last_updated' => 'datetime',
    ];

    public $timestamps = false; // migration uses last_updated timestamp rather than created_at/updated_at

    public function patient()
    {
        return $this->belongsTo(\App\Models\PatientDetail::class, 'patient_id');
    }

    /**
     * Convenience accessor: returns invoices for the statement's patient.
     */
    public function invoices()
    {
        return $this->hasMany(\App\Models\Invoice::class, 'patient_id', 'patient_id');
    }

    /**
     * Recalculate totals for this billing statement from invoices (and invoice payments).
     * - total_amount = sum of invoice amounts for patient
     * - outstanding_balance = sum of invoice balance_due for patient
     *
     * This method updates the model and saves it.
     */
    public function recalculateTotals(): self
    {
        $patientId = $this->patient_id;

        // Use Invoice fields (amount and balance_due) which should be kept up-to-date when payments are applied.
        $totals = \App\Models\Invoice::where('patient_id', $patientId)
            ->selectRaw('COALESCE(SUM(amount),0) as total_amount, COALESCE(SUM(balance_due),0) as outstanding_balance')
            ->first();

        $this->total_amount = (float) ($totals->total_amount ?? 0);
        $this->outstanding_balance = (float) ($totals->outstanding_balance ?? 0);
        $this->last_updated = now();
        $this->save();

        return $this;
    }
}
