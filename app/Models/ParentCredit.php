<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ParentCredit extends Model
{
    protected $fillable = [
        'parent_id',
        'origin_fee_invoice_id',
        'source_reference',
        'total_amount',
        'balance_amount',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ParentGuardian::class, 'parent_id');
    }

    public function originInvoice(): BelongsTo
    {
        return $this->belongsTo(FeeInvoice::class, 'origin_fee_invoice_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(ParentCreditApplication::class);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open' && (float) $this->balance_amount > 0;
    }
}
