<?php
// Deploy to: app/Models/Term.php
// REPLACES existing file — adds school_days_count and next_term_begins.

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Term extends Model
{
    protected $fillable = [
        'academic_session_id',
        'name',
        'is_active',
        'start_date',
        'end_date',
        'school_days_count',   // how many days the school was open
        'next_term_begins',    // date printed on report cards
    ];

    protected $casts = [
        'is_active'         => 'boolean',
        'start_date'        => 'date',
        'end_date'          => 'date',
        'next_term_begins'  => 'date',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class, 'academic_session_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(FeeInvoice::class);
    }

    public static function current(): ?self
    {
        return self::where('is_active', true)->first();
    }
}
