<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prescription extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'patient_id',
        'staff_id',
        'medication_id',
        'dosage',
        'frequency',
        'duration',
        'start_date',
        'end_date',
        'status',
        'instructions',
    ];

    // Relationships
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function medication()
    {
        return $this->belongsTo(Medication::class);
    }
}
