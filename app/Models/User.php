<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    // Relationships
    public function parentProfile(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ParentGuardian::class);
    }

    // Helpers
    public function isAdmin(): bool
    {
        return in_array($this->user_type, ['super_admin', 'admin']);
    }

    public function isTeacher(): bool
    {
        return $this->user_type === 'teacher';
    }

    public function isParent(): bool
    {
        return $this->user_type === 'parent';
    }
}
