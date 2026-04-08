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
        // JuicyWay virtual account (legacy — kept during pilot)
        'juicyway_customer_id',
        'juicyway_wallet_id',
        'juicyway_account_id',
        'juicyway_account_number',
        'juicyway_bank_name',
        'juicyway_bank_code',
        'juicyway_wallet_status',
        // BudPay virtual account (new)
        'budpay_customer_code',
        'budpay_account_number',
        'budpay_bank_name',
        'budpay_bank_code',
        'budpay_wallet_status',
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
     * Returns true if this parent has an active BudPay NUBAN.
     * This is the primary check going forward.
     */
    public function hasVirtualAccount(): bool
    {
        return ! empty($this->budpay_account_number);
    }

    /**
     * The active NUBAN to show parents and use for payment matching.
     * Returns BudPay number if provisioned, falls back to JuicyWay
     * during the transition period.
     */
    public function getActiveAccountNumberAttribute(): ?string
    {
        return $this->budpay_account_number
            ?? $this->juicyway_account_number
            ?? null;
    }

    /**
     * The active bank name — BudPay if provisioned, JuicyWay fallback.
     */
    public function getActiveBankNameAttribute(): ?string
    {
        if (! empty($this->budpay_account_number)) {
            return $this->budpay_bank_name;
        }
        return $this->juicyway_bank_name;
    }

    /**
     * Overall wallet status — reflects BudPay status if a provisioning
     * attempt has been made, otherwise JuicyWay status.
     */
    public function getWalletStatusAttribute(): ?string
    {
        return $this->budpay_wallet_status ?? $this->juicyway_wallet_status;
    }

    public function isWalletProvisioning(): bool
    {
        return ($this->budpay_wallet_status ?? $this->juicyway_wallet_status) === 'pending';
    }

    public function isWalletFailed(): bool
    {
        return ($this->budpay_wallet_status ?? $this->juicyway_wallet_status) === 'failed';
    }
}
