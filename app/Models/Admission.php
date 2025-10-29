<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Admission extends Model
{
    use SoftDeletes;

    

    protected $fillable = [
        'patient_id',
        'evaluation_id',
        'admitted_by',
        'assigned_psychiatrist_id',
        'care_level_id',
        'admission_date',
        'admission_reason',
        'room_number',
        'status',
        'created_by',
        'last_modified_by',
    ];

    public function patient()
    {
        return $this->belongsTo(PatientDetail::class, 'patient_id');
    }

    public function evaluation()
    {
        return $this->belongsTo(PatientEvaluation::class, 'evaluation_id');
    }

    public function assignedNurses()
{
    return $this->belongsToMany(User::class, 'nurse_patient_assignments', 'admission_id', 'nurse_id')
                ->withPivot('shift', 'assigned_date', 'notes')
                ->withTimestamps();
}

public function nurseAssignments()
{
    return $this->hasMany(NursePatientAssignment::class, 'admission_id');
}

public function careLevel()
{
    return $this->belongsTo(\App\Models\CareLevel::class, 'care_level_id');
}

}
