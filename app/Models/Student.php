<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    protected $fillable = [
        'admission_number', 'first_name', 'last_name', 'other_name',
        'gender', 'date_of_birth', 'photo', 'status', 'notes',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(ParentGuardian::class, 'parent_student', 'student_id', 'parent_id')
                    ->withPivot(['relationship', 'is_primary_contact'])
                    ->withTimestamps();
    }

    public function enrolments(): HasMany
    {
        return $this->hasMany(Enrolment::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(Result::class);
    }

    public function currentEnrolment(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        $activeSession = AcademicSession::current();
        return $this->hasOne(Enrolment::class)
                    ->where('academic_session_id', optional($activeSession)->id);
    }
}
