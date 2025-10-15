<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    protected $fillable = [
        'patient_id',
        'evaluated_by',
        'risk_level',
        'notes',
        'scores',
    ];

    protected $casts = [
        'scores' => 'array',
    ];
    

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }
}

