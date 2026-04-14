<?php
// Deploy to: app/Models/StudentTermComment.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentTermComment extends Model
{
    protected $table = 'student_term_comments';

    protected $fillable = [
        'student_id',
        'term_id',
        'teacher_comment',
        'head_teacher_comment',
        'written_by',
        'reviewed_by',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function writer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'written_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
