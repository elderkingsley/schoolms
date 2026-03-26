<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeStructure extends Model
{
    protected $fillable = [
        'fee_item_id', 'academic_session_id',
        'term_id', 'school_class_id', 'amount',
    ];

    protected $casts = ['amount' => 'decimal:2'];

    public function feeItem(): BelongsTo
    {
        return $this->belongsTo(FeeItem::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class, 'academic_session_id');
    }

    public function term(): BelongsTo
    {
        return $this->belongsTo(Term::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }
}
