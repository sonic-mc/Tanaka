<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    protected $fillable = [
        'patient_code', 'first_name', 'last_name', 'gender', 'dob',
        'contact_number', 'admission_date', 'admission_reason',
        'admitted_by', 'room_number', 'status', 'current_care_level_id'
    ];


     // Relationship to User who admitted this patient
     public function admittedBy()
     {
         return $this->belongsTo(User::class, 'admitted_by');
     }

    public function careLevel()
    {
        return $this->belongsTo(CareLevel::class, 'current_care_level_id');
    }

    public function evaluations()
    {
        return $this->hasMany(Evaluation::class);
    }

    public function progressReports()
    {
        return $this->hasMany(ProgressReport::class);
    }

    public function discharges()
    {
        return $this->hasOne(Discharge::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function billingStatement()
    {
        return $this->hasOne(BillingStatement::class);
    }

    public function incidentReports()
    {
        return $this->hasMany(IncidentReport::class);
    }
}

