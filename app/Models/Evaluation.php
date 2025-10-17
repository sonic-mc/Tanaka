<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    protected $fillable = [
        'patient_id',
        'evaluated_by',
        'notes',
        'risk_level',
        'scores',
    ];

    protected $casts = [
        'scores'     => 'array',   // stored as JSON, used as array
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluated_by');
    }
}
