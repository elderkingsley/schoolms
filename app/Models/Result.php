<?php

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
        'submitted_by', 'submitted_at',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'ca_score'     => 'integer',
        'exam_score'   => 'integer',
        'total'        => 'integer',
        'submitted_at' => 'datetime',
    ];

    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function subject(): BelongsTo { return $this->belongsTo(Subject::class); }
    public function term(): BelongsTo    { return $this->belongsTo(Term::class); }
    public function submittedBy(): BelongsTo { return $this->belongsTo(User::class, 'submitted_by'); }

    public function computeAndSave(): void
    {
        $total   = min(100, ($this->ca_score ?? 0) + ($this->exam_score ?? 0));
        $grading = Subject::gradeFor($total);
        $this->update(['total' => $total, 'grade' => $grading['grade'], 'remark' => $grading['remark']]);
    }

    public function isSubmitted(): bool { return $this->submitted_at !== null; }
}
