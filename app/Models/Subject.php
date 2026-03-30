<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    protected $fillable = ['name', 'code', 'is_active', 'sort_order'];

    protected $casts = ['is_active' => 'boolean'];

    // ── Relationships ────────────────────────────────────────────────────────

    public function classes(): BelongsToMany
    {
        return $this->belongsToMany(SchoolClass::class, 'class_subjects')
                    ->withPivot(['teacher_id', 'academic_session_id'])
                    ->withTimestamps();
    }

    public function results(): HasMany
    {
        return $this->hasMany(Result::class);
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // ── Grading scale ────────────────────────────────────────────────────────

    /**
     * Derive grade letter and remark from a percentage total (0–100).
     * Nigerian primary school standard, percentage-based.
     *
     * Returns ['grade' => 'A', 'remark' => 'Excellent']
     */
    public static function gradeFor(int $total): array
    {
        return match (true) {
            $total >= 75 => ['grade' => 'A', 'remark' => 'Excellent'],
            $total >= 65 => ['grade' => 'B', 'remark' => 'Very Good'],
            $total >= 55 => ['grade' => 'C', 'remark' => 'Good'],
            $total >= 45 => ['grade' => 'D', 'remark' => 'Fair'],
            $total >= 35 => ['grade' => 'E', 'remark' => 'Pass'],
            default      => ['grade' => 'F', 'remark' => 'Fail'],
        };
    }
}
