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
        return $this->hasMany(\App\Models\Payment::class, 'invoice_id');
    }
}
