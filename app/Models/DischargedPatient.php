<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DischargedPatient extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'patient_id',
        'admission_id',
        'discharged_by',
        'discharge_date',
        'discharge_notes',
        'follow_up_plan',
        'referral_facility',
        'requires_follow_up',
        'created_by',
        'last_modified_by',
    ];

    protected $casts = [
        'discharge_date' => 'date',
        'requires_follow_up' => 'boolean',
    ];

    public function admission()
    {
        return $this->belongsTo(\App\Models\Admission::class);
    }

    public function patient()
    {
        // Assuming the model is App\Models\PatientDetail for the patient_details table
        return $this->belongsTo(\App\Models\PatientDetail::class, 'patient_id');
    }

    public function dischargedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'discharged_by');
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function lastModifiedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'last_modified_by');
    }
}