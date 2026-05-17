<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParentCreditApplication extends Model
{
    protected $fillable = [
        'parent_credit_id',
        'fee_invoice_id',
        'fee_payment_id',
        'amount',
        'reference',
        'applied_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function credit(): BelongsTo
    {
        return $this->belongsTo(ParentCredit::class, 'parent_credit_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(FeeInvoice::class, 'fee_invoice_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(FeePayment::class, 'fee_payment_id');
    }
}
