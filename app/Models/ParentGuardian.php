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
        // JuicyWay virtual account
        'juicyway_customer_id',
        'juicyway_wallet_id',
        'juicyway_account_id',
        'juicyway_account_number',
        'juicyway_bank_name',
        'juicyway_bank_code',
        'juicyway_wallet_status',
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

    public function hasVirtualAccount(): bool
    {
        return ! empty($this->juicyway_account_number);
    }

    public function isWalletProvisioning(): bool
    {
        return $this->juicyway_wallet_status === 'pending';
    }

    public function isWalletFailed(): bool
    {
        return $this->juicyway_wallet_status === 'failed';
    }
}
