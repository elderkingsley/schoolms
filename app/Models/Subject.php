<?php
// Deploy to: app/Models/Subject.php
// REPLACES existing file — grading scale corrected to Nurtureville Primary standard.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    protected $fillable = ['name', 'code', 'is_active', 'sort_order'];
    protected $casts    = ['is_active' => 'boolean'];

    // ── Relationships ─────────────────────────────────────────────────────────

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

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeActive($query)  { return $query->where('is_active', true); }
    public function scopeOrdered($query) { return $query->orderBy('sort_order')->orderBy('name'); }

    // ── Grading scale ─────────────────────────────────────────────────────────

    /**
     * Nurtureville Primary grading scale (percentage-based, 0–100).
     *
     * ┌──────────┬───────┬───────────────┐
     * │ Range    │ Grade │ Remark        │
     * ├──────────┼───────┼───────────────┤
     * │ 90–100   │ A+    │ Distinction   │
     * │ 70–89    │ A     │ Excellent     │
     * │ 60–69    │ B     │ Very Good     │
     * │ 50–59    │ C     │ Good          │
     * │ 40–49    │ D     │ Average       │
     * │  0–39    │ E     │ Below Average │
     * └──────────┴───────┴───────────────┘
     *
     * Returns ['grade' => 'A+', 'remark' => 'Distinction']
     *
     * NOTE: After deploying the corrected scale, run the recompute script
     * to fix all existing stored grade/remark values:
     *   php artisan tinker --execute="Result::all()->each(fn(\$r) => \$r->computeAndSave());"
     */
    public static function gradeFor(int $total): array
    {
        return match (true) {
            $total >= 90 => ['grade' => 'A+', 'remark' => 'Distinction'],
            $total >= 70 => ['grade' => 'A',  'remark' => 'Excellent'],
            $total >= 60 => ['grade' => 'B',  'remark' => 'Very Good'],
            $total >= 50 => ['grade' => 'C',  'remark' => 'Good'],
            $total >= 40 => ['grade' => 'D',  'remark' => 'Average'],
            default      => ['grade' => 'E',  'remark' => 'Below Average'],
        };
    }

    /**
     * The ordered list of valid remark strings for the teacher dropdown.
     * Ordered from highest to lowest so the dropdown is intuitive.
     */
    public static function remarkOptions(): array
    {
        return ['Distinction', 'Excellent', 'Very Good', 'Good', 'Average', 'Below Average'];
    }

    /**
     * Returns the remark string that corresponds to a given score.
     * Used to pre-select the dropdown default when a score is already entered.
     */
    public static function remarkForScore(int $total): string
    {
        return self::gradeFor($total)['remark'];
    }
}
