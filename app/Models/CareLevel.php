<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CareLevel extends Model
{
    protected $fillable = ['name', 'description'];

    public function patients()
    {
        return $this->hasMany(Patient::class, 'current_care_level_id');
    }
}
