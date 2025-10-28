<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * Relationships
     */

    public function incidentReports()
    {
        return $this->hasMany(\App\Models\IncidentReport::class, 'reported_by');
    }

    public function assignedPatients()
    {
        return $this->belongsToMany(Admission::class, 'nurse_patient_assignments', 'nurse_id', 'admission_id')
                    ->withPivot('shift', 'assigned_date', 'notes')
                    ->withTimestamps();
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Helper: Check if user has admin privileges.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Helper: Assign a role directly.
     */
    public function assignSystemRole(string $roleName): void
    {
        $this->role = $roleName;
        $this->save();
    }

    /**
     * Scope: Staff considered “clinical” for assignment/selection purposes.
     */
    public function scopeClinicalStaff($query)
    {
        return $query->whereIn('role', ['psychiatrist', 'nurse']);
    }
}
