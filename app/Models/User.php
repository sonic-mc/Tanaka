<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * Relationships
     */

    // If you're using Spatie, you don't need this custom belongsTo Role,
    // because roles are handled via the roles/permissions tables automatically.
    // However, we’ll keep it safely for backward compatibility.
    public function role()
    {
        return $this->belongsTo(\Spatie\Permission\Models\Role::class, 'role_id', 'id');
    }

    public function incidentReports()
    {
        return $this->hasMany(\App\Models\IncidentReport::class, 'reported_by');
    }

    public function assignedPatients()
    {
        return $this->hasMany(\App\Models\Patient::class, 'assigned_nurse_id');
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
        return $this->hasRole('admin');
    }

    /**
     * Helper: Assign a role dynamically (wrapper for Spatie).
     */
    public function assignSystemRole(string $roleName): void
    {
        $this->syncRoles([$roleName]);
    }

      // Staff considered “clinicians” for assignment/selection purposes
      public function scopeClinicalStaff($query)
      {
          return $query->whereIn('role', ['psychiatrist', 'nurse']);
      }
}
