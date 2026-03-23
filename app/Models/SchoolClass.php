<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolClass extends Model
{
    protected $fillable = ['name', 'level', 'order'];

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'class_subjects')
                    ->withPivot(['teacher_id', 'academic_session_id'])
                    ->withTimestamps();
    }

    public function enrolments(): HasMany
    {
        return $this->hasMany(Enrolment::class);
    }
}
