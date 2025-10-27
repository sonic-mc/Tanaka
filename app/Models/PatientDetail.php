<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;


class PatientDetail extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        // Identification
        'patient_code',
        'first_name',
        'middle_name',
        'last_name',
        'gender',
        'dob',
        'national_id_number',
        'passport_number',
        'photo',

        // Contact & Demographics
        'email',
        'contact_number',
        'residential_address',
        'race',
        'religion',
        'language',
        'denomination',
        'marital_status',
        'occupation',

        // Medical Info
        'blood_group',
        'allergies',
        'disabilities',
        'special_diet',
        'medical_aid_provider',
        'medical_aid_number',
        'special_medical_requirements',
        'current_medications',
        'past_medical_history',

        // Next of Kin
        'next_of_kin_name',
        'next_of_kin_relationship',
        'next_of_kin_contact_number',
        'next_of_kin_email',
        'next_of_kin_address',

        // Administrative
        'created_by',
        'last_modified_by',
    ];

    protected $casts = [
        'dob' => 'date',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function lastModifier()
    {
        return $this->belongsTo(User::class, 'last_modified_by');
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . ($this->middle_name ? $this->middle_name . ' ' : '') . $this->last_name);
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(PatientEvaluation::class, 'patient_id');
    }
}
