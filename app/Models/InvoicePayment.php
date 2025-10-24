<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoicePayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'invoice_payments';

    protected $fillable = [
        'invoice_id',
        'patient_id',
        'received_by',
        'amount',
        'method',
        'transaction_ref',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function patient()
    {
        return $this->belongsTo(\App\Models\PatientDetail::class, 'patient_id');
    }

    public function receiver()
    {
        return $this->belongsTo(\App\Models\User::class, 'received_by');
    }
}
