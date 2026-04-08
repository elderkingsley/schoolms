<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ParentGuardian extends Model
{
    protected $table = 'parents';

    protected $fillable = [
        'user_id',
        'phone',
        'address',
        'occupation',
        'relationship',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        '_temp_name',
        '_temp_email',
        // JuicyWay (legacy)
        'juicyway_customer_id',
        'juicyway_wallet_id',
        'juicyway_account_id',
        'juicyway_account_number',
        'juicyway_bank_name',
        'juicyway_bank_code',
        'juicyway_wallet_status',
        // BudPay (pilot — superseded by Korapay)
        'budpay_customer_code',
        'budpay_account_number',
        'budpay_bank_name',
        'budpay_bank_code',
        'budpay_wallet_status',
        // Korapay (current)
        'korapay_account_reference',
        'korapay_account_number',
        'korapay_bank_name',
        'korapay_bank_code',
        'korapay_wallet_status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'parent_student', 'parent_id', 'student_id')
                    ->withPivot(['relationship', 'is_primary_contact'])
                    ->withTimestamps();
    }

    // ── Virtual account helpers ───────────────────────────────────────────────

    /**
     * Returns true if this parent has an active permanent NUBAN.
     * Checks Korapay first, then BudPay, then JuicyWay.
     */
    public function hasVirtualAccount(): bool
    {
        return ! empty($this->korapay_account_number)
            || ! empty($this->budpay_account_number)
            || ! empty($this->juicyway_account_number);
    }

    /**
     * The active NUBAN to display and use for payment matching.
     * Priority: Korapay → BudPay → JuicyWay
     */
    public function getActiveAccountNumberAttribute(): ?string
    {
        return $this->korapay_account_number
            ?? $this->budpay_account_number
            ?? $this->juicyway_account_number
            ?? null;
    }

    /**
     * The active bank name — follows same priority as account number.
     */
    public function getActiveBankNameAttribute(): ?string
    {
        if (! empty($this->korapay_account_number)) {
            return $this->korapay_bank_name;
        }
        if (! empty($this->budpay_account_number)) {
            return $this->budpay_bank_name;
        }
        return $this->juicyway_bank_name;
    }

    /**
     * The account reference used to match Korapay webhooks.
     * Only set for Korapay accounts.
     */
    public function getActiveAccountReferenceAttribute(): ?string
    {
        return $this->korapay_account_reference ?? null;
    }

    /**
     * Overall provisioning status — reflects the most recent provider.
     */
    public function getWalletStatusAttribute(): ?string
    {
        return $this->korapay_wallet_status
            ?? $this->budpay_wallet_status
            ?? $this->juicyway_wallet_status;
    }

    public function isWalletProvisioning(): bool
    {
        return $this->wallet_status === 'pending';
    }

    public function isWalletFailed(): bool
    {
        return $this->wallet_status === 'failed';
    }
}
