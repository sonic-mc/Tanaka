<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NursePatientAssignment extends Model
{
    protected $table = 'nurse_patient_assignments';

    protected $fillable = [
        'nurse_id',
        'admission_id',
        'shift',
        'assigned_date',
        'notes',
        'assigned_by',
    ];

    protected $casts = [
        'assigned_date' => 'date',
    ];

    public function nurse()
    {
        return $this->belongsTo(User::class, 'nurse_id');
    }

    public function admission()
    {
        return $this->belongsTo(Admission::class, 'admission_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
