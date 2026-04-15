<?php
// Deploy to: app/Models/Result.php
// REPLACES existing file — adds class_average/lowest/highest, remark is now teacher-chosen.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Result extends Model
{
    protected $fillable = [
        'student_id', 'subject_id', 'term_id',
        'ca_score', 'exam_score', 'total',
        'grade', 'remark',
        'admin_comment',
        'class_average', 'class_lowest', 'class_highest',
        'submitted_by', 'submitted_at',
        'is_published',
    ];

    protected $casts = [
        'is_published'  => 'boolean',
        'ca_score'      => 'integer',
        'exam_score'    => 'integer',
        'total'         => 'integer',
        'class_lowest'  => 'integer',
        'class_highest' => 'integer',
        'class_average' => 'decimal:2',
        'submitted_at'  => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function student(): BelongsTo   { return $this->belongsTo(Student::class); }
    public function subject(): BelongsTo   { return $this->belongsTo(Subject::class); }
    public function term(): BelongsTo      { return $this->belongsTo(Term::class); }
    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    // ── Core computation ──────────────────────────────────────────────────────

    /**
     * Recomputes total and grade from current ca_score + exam_score,
     * then persists. Does NOT overwrite a teacher-chosen remark —
     * remark is managed separately by the teacher via the dropdown.
     *
     * Called by the tinker recompute script after a grading scale change:
     *   Result::all()->each(fn($r) => $r->computeAndSave());
     */
    public function computeAndSave(): void
    {
        $total   = min(100, ($this->ca_score ?? 0) + ($this->exam_score ?? 0));
        $grading = Subject::gradeFor($total);

        $this->update([
            'total' => $total,
            'grade' => $grading['grade'],
            // remark is intentionally NOT updated here —
            // it is the teacher's chosen remark from the dropdown.
            // If remark is currently null (legacy), seed it from the grade scale.
            'remark' => $this->remark ?? $grading['remark'],
        ]);
    }

    /**
     * Computes and stores class_average, class_lowest, class_highest
     * for all published results of a given subject in a given term.
     *
     * Called by ReportCardController::publishResults() at publish time.
     * Runs once per subject per term — updates all rows for that subject/term.
     */
    public static function computeClassStats(int $subjectId, int $termId): void
    {
        $rows = self::where('subject_id', $subjectId)
            ->where('term_id', $termId)
            ->whereNotNull('total')
            ->get();

        if ($rows->isEmpty()) return;

        $avg     = round($rows->avg('total'), 2);
        $lowest  = $rows->min('total');
        $highest = $rows->max('total');

        // Update all rows for this subject/term with the same class stats
        self::where('subject_id', $subjectId)
            ->where('term_id', $termId)
            ->update([
                'class_average' => $avg,
                'class_lowest'  => $lowest,
                'class_highest' => $highest,
            ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isSubmitted(): bool { return $this->submitted_at !== null; }
}
