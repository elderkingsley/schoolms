<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeeInvoice extends Model
{
    protected $fillable = [
        'student_id', 'term_id',
        'total_amount', 'amount_paid', 'balance',
        'status', 'sent_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'amount_paid'  => 'decimal:2',
        'balance'      => 'decimal:2',
        'sent_at'      => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(FeePayment::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(FeeInvoiceItem::class);
    }

    // ── Scopes ────────────────────────────────────────────────────────────────

    public function scopeDraft($query)
    {
        return $query->whereNull('sent_at');
    }

    public function scopeSent($query)
    {
        return $query->whereNotNull('sent_at');
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public function isDraft(): bool
    {
        return $this->sent_at === null;
    }

    public function isSent(): bool
    {
        return $this->sent_at !== null;
    }

    public function recalculateTotal(): void
    {
        $total = $this->items()->sum('amount');
        $paid  = $this->payments()->sum('amount');

        $this->update([
            'total_amount' => $total,
            'amount_paid'  => $paid,
            'balance'      => max(0, $total - $paid),
            'status'       => $paid >= $total ? 'paid' : ($paid > 0 ? 'partial' : 'unpaid'),
        ]);
    }
}
