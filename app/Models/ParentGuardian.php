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

    // ── Provider Account Helpers ─────────────────────────────────────────────

    /**
     * Check if parent has a JuicyWay account provisioned.
     */
    public function hasJuicyWayAccount(): bool
    {
        return ! empty($this->juicyway_account_number);
    }

    /**
     * Check if parent has a BudPay account provisioned.
     */
    public function hasBudPayAccount(): bool
    {
        return ! empty($this->budpay_account_number);
    }

    /**
     * Check if parent has an account for the given provider.
     */
    public function hasProviderAccount(string $provider): bool
    {
        return match($provider) {
            'juicyway' => $this->hasJuicyWayAccount(),
            'budpay'   => $this->hasBudPayAccount(),
            default    => false,
        };
    }

    /**
     * Check if parent needs provisioning for the given provider.
     */
    public function needsProviderAccount(string $provider): bool
    {
        return ! $this->hasProviderAccount($provider);
    }

    /**
     * Get the current active wallet provider from config or settings.
     */
    public static function getActiveWalletProvider(): string
    {
        // 1. Check admin override in school_settings
        $setting = SchoolSetting::get('wallet_provider');
        if ($setting && in_array($setting, ['budpay', 'juicyway'])) {
            return $setting;
        }

        // 2. Fall back to config (which reads from .env)
        return config('services.wallet.default', 'budpay');
    }

    // ── Virtual account helpers ───────────────────────────────────────────────

    /**
     * Returns true if this parent has an active permanent NUBAN.
     * Checks based on the currently active provider preference.
     */
    public function hasVirtualAccount(): bool
    {
        $provider = self::getActiveWalletProvider();
        return $this->hasProviderAccount($provider);
    }

    /**
     * The active NUBAN to display and use for payment matching.
     * Priority: Based on active wallet provider setting.
     */
    public function getActiveAccountNumberAttribute(): ?string
    {
        $provider = self::getActiveWalletProvider();

        if ($provider === 'juicyway' && $this->hasJuicyWayAccount()) {
            return $this->juicyway_account_number;
        }

        if ($provider === 'budpay' && $this->hasBudPayAccount()) {
            return $this->budpay_account_number;
        }

        // Fallback: return whatever is available (legacy support)
        return $this->juicyway_account_number
            ?? $this->budpay_account_number
            ?? $this->korapay_account_number
            ?? null;
    }

    /**
     * The active bank name — follows same priority as account number.
     */
    public function getActiveBankNameAttribute(): ?string
    {
        $provider = self::getActiveWalletProvider();

        if ($provider === 'juicyway' && $this->hasJuicyWayAccount()) {
            return $this->juicyway_bank_name;
        }

        if ($provider === 'budpay' && $this->hasBudPayAccount()) {
            return $this->budpay_bank_name;
        }

        // Fallback
        if (! empty($this->juicyway_account_number)) {
            return $this->juicyway_bank_name;
        }
        if (! empty($this->budpay_account_number)) {
            return $this->budpay_bank_name;
        }
        return $this->korapay_bank_name;
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
     * Overall provisioning status — reflects the active provider.
     */
    public function getWalletStatusAttribute(): ?string
    {
        $provider = self::getActiveWalletProvider();

        if ($provider === 'juicyway') {
            return $this->juicyway_wallet_status;
        }

        if ($provider === 'budpay') {
            return $this->budpay_wallet_status;
        }

        // Fallback
        return $this->juicyway_wallet_status
            ?? $this->budpay_wallet_status
            ?? $this->korapay_wallet_status;
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
