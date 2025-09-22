<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Medication extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'brand',
        'dosage_form',
        'strength',
        'quantity',
        'reorder_level',
        'unit_price',
        'expiry_date',
        'batch_number',
        'manufacturer',
        'supplier',
        'status',
    ];

    // Relationships
    public function prescriptions()
    {
        return $this->hasMany(Prescription::class);
    }
}
