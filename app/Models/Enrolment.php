<?php
// Deploy to: app/Models/Enrolment.php
// REPLACES existing file — adds times_present and times_absent.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enrolment extends Model
{
    protected $fillable = [
        'student_id',
        'school_class_id',
        'academic_session_id',
        'enrolled_at',
        'status',
        'times_present',   // entered by teacher
        'times_absent',    // entered by teacher independently
    ];

    protected $casts = [
        'enrolled_at' => 'date',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class, 'academic_session_id');
    }
}
