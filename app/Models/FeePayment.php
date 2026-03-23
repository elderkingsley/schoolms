<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeePayment extends Model
{
    protected $fillable = [
        'fee_invoice_id', 'amount', 'method',
        'receipt_number', 'reference', 'recorded_by', 'paid_at',
    ];

    protected $casts = ['paid_at' => 'datetime'];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(FeeInvoice::class, 'fee_invoice_id');
    }
}
