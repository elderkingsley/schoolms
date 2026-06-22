<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeInvoiceAdjustment extends Model
{
    protected $fillable = [
        'fee_invoice_id',
        'adjusted_by',
        'action',
        'old_total_amount',
        'new_total_amount',
        'old_amount_paid',
        'new_amount_paid',
        'old_balance',
        'new_balance',
        'credit_adjustment_amount',
        'paygrid_sync_status',
        'notified_at',
        'before_snapshot',
        'after_snapshot',
        'metadata',
    ];

    protected $casts = [
        'old_total_amount' => 'decimal:2',
        'new_total_amount' => 'decimal:2',
        'old_amount_paid' => 'decimal:2',
        'new_amount_paid' => 'decimal:2',
        'old_balance' => 'decimal:2',
        'new_balance' => 'decimal:2',
        'credit_adjustment_amount' => 'decimal:2',
        'notified_at' => 'datetime',
        'before_snapshot' => 'array',
        'after_snapshot' => 'array',
        'metadata' => 'array',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(FeeInvoice::class, 'fee_invoice_id');
    }

    public function adjustedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adjusted_by');
    }
}
