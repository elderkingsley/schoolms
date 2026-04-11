<?php
// Deploy to: app/Models/SchoolClass.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolClass extends Model
{
    protected $fillable = ['name', 'level', 'arm', 'form_teacher_id', 'assistant_teacher_id', 'order', 'result_type'];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function formTeacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'form_teacher_id');
    }

    public function assistantTeacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assistant_teacher_id');
    }

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


    // ── Result type helpers ───────────────────────────────────────────────────

    /**
     * Returns true if this class uses remark-only results (e.g. Nursery).
     * Use this everywhere instead of checking result_type === 'remark_only' directly.
     */
    public function isRemarkOnly(): bool
    {
        return $this->result_type === 'remark_only';
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    /**
     * Human-readable display name.
     * "Primary 3" when no arm, "Primary 3 — Gold" when arm is set.
     * Use this everywhere a class name is shown to humans.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->arm
            ? "{$this->name} — {$this->arm}"
            : $this->name;
    }

    /**
     * Short display for tight spaces (report card headers, etc.)
     * "Primary 3 (Gold)" or "Primary 3"
     */
    public function getShortNameAttribute(): string
    {
        return $this->arm
            ? "{$this->name} ({$this->arm})"
            : $this->name;
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeOrdered($query)
    {
        return $query->orderBy('order')->orderBy('name')->orderBy('arm');
    }
}
