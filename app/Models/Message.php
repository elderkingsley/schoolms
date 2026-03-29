<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Message extends Model
{
    protected $fillable = [
        'sender_id', 'subject', 'body',
        'recipient_type', 'school_class_id', 'term_id',
        'recipient_count', 'sent_at',
    ];

    protected $casts = ['sent_at' => 'datetime'];

    // ── Relationships ────────────────────────────────────────────────────────

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(MessageRecipient::class);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    public function readCount(): int
    {
        return $this->recipients()->whereNotNull('read_at')->count();
    }

    /**
     * Resolve the full set of ParentGuardian IDs for a given filter type.
     * Called by SendBulkMessageJob before creating MessageRecipient rows.
     *
     * Filter types:
     *   all        — every parent with a portal account
     *   class      — parents of students in a specific class (current session)
     *   term       — parents of students enrolled in a specific term's session
     *   unpaid     — parents of students with unpaid/partial invoices this term
     *   individual — handled separately (IDs passed directly)
     */
    public static function resolveRecipients(
        string   $type,
        ?int     $classId = null,
        ?int     $termId  = null
    ): Collection {
        return match ($type) {

            'all' => ParentGuardian::whereNotNull('user_id')->get(),

            'class' => ParentGuardian::whereNotNull('user_id')
                ->whereHas('students.enrolments', function ($q) use ($classId) {
                    $q->where('school_class_id', $classId)
                      ->where('status', 'active')
                      ->whereHas('session', fn($s) => $s->where('is_active', true));
                })->get(),

            'term' => ParentGuardian::whereNotNull('user_id')
                ->whereHas('students.enrolments', function ($q) use ($termId) {
                    $q->where('status', 'active')
                      ->whereHas('session', function ($s) use ($termId) {
                          $s->whereHas('terms', fn($t) => $t->where('id', $termId));
                      });
                })->get(),

            'unpaid' => ParentGuardian::whereNotNull('user_id')
                ->whereHas('students.enrolments', function ($q) {
                    $q->where('status', 'active')
                      ->whereHas('session', fn($s) => $s->where('is_active', true));
                })
                ->whereHas('students', function ($q) use ($termId) {
                    $q->whereHas('feeInvoices', function ($inv) use ($termId) {
                        $inv->whereIn('status', ['unpaid', 'partial'])
                            ->when($termId, fn($i) => $i->where('term_id', $termId));
                    });
                })->get(),

            default => collect(),
        };
    }

    /**
     * Human-readable label for the recipient_type stored on this message.
     */
    public function recipientLabel(): string
    {
        return match ($this->recipient_type) {
            'all'        => 'All Parents',
            'class'      => 'Class: ' . ($this->schoolClass?->name ?? '—'),
            'term'       => 'Term: ' . ($this->term?->name ?? '—'),
            'unpaid'     => 'Parents with Unpaid Fees',
            'individual' => 'Individual (' . $this->recipient_count . ')',
            default      => ucfirst($this->recipient_type),
        };
    }
}
