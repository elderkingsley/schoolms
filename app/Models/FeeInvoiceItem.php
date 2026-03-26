<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeInvoiceItem extends Model
{
    protected $fillable = [
        'fee_invoice_id', 'fee_item_id',
        'item_name', 'amount', 'added_by',
    ];

    protected $casts = ['amount' => 'decimal:2'];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(FeeInvoice::class, 'fee_invoice_id');
    }

    public function feeItem(): BelongsTo
    {
        return $this->belongsTo(FeeItem::class);
    }
}
