<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * TeacherRegistration
 *
 * Stores self-registration requests from prospective teachers
 * and teaching assistants. An admin reviews each request.
 *
 * Status flow: pending → approved | rejected
 *
 * On approval: a User account is created, credentials emailed,
 * and user_id is populated here for the audit trail.
 */
class TeacherRegistration extends Model
{
    protected $fillable = [
        'name', 'email', 'phone', 'role',
        'notes', 'status', 'user_id',
        'reviewed_by', 'reviewed_at', 'rejection_reason',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isPending(): bool  { return $this->status === 'pending'; }
    public function isApproved(): bool { return $this->status === 'approved'; }
    public function isRejected(): bool { return $this->status === 'rejected'; }

    public function getRoleLabelAttribute(): string
    {
        return match($this->role) {
            'teaching_assistant' => 'Teaching Assistant',
            default              => 'Teacher',
        };
    }
}
