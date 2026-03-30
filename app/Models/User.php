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
        'name', 'email', 'password',
        'user_type', 'is_active',
        'force_password_change', 'phone',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at'      => 'datetime',
            'password'               => 'hashed',
            'is_active'              => 'boolean',
            'force_password_change'  => 'boolean',
        ];
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function parentProfile(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ParentGuardian::class);
    }

    public function formClasses(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SchoolClass::class, 'form_teacher_id');
    }

    // ── Type helpers ──────────────────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return in_array($this->user_type, ['super_admin', 'admin']);
    }

    public function isSuperAdmin(): bool
    {
        return $this->user_type === 'super_admin';
    }

    public function isTeacher(): bool
    {
        return $this->user_type === 'teacher';
    }

    public function isParent(): bool
    {
        return $this->user_type === 'parent';
    }

    public function isAccountant(): bool
    {
        return $this->user_type === 'accountant';
    }

    // ── Display helpers ───────────────────────────────────────────────────────

    public static function userTypeLabel(string $type): string
    {
        return match($type) {
            'super_admin' => 'Super Admin',
            'admin'       => 'Admin',
            'teacher'     => 'Teacher',
            'accountant'  => 'Accountant',
            'parent'      => 'Parent',
            default       => ucfirst($type),
        };
    }

    public function getTypeLabelAttribute(): string
    {
        return self::userTypeLabel($this->user_type);
    }
}
