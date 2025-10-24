<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConsultationFee extends Model
{
    use HasFactory;

    // Guarded or fillable - ensure mass assignment is allowed for controlled fields
    protected $fillable = [
        'age_group',
        'fee_amount',
        'description',
    ];

    // Optional: cast fee_amount to decimal for consistent formatting
    protected $casts = [
        'fee_amount' => 'decimal:2',
    ];
}
