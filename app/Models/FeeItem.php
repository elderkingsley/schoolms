<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeeItem extends Model
{
    protected $fillable = ['name', 'description', 'type', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function isCompulsory(): bool
    {
        return $this->type === 'compulsory';
    }

    public function isOptional(): bool
    {
        return $this->type === 'optional';
    }

    public function feeStructures(): HasMany
    {
        return $this->hasMany(FeeStructure::class);
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(FeeInvoiceItem::class);
    }

    // Scope: active items only
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCompulsory($query)
    {
        return $query->where('type', 'compulsory');
    }

    public function scopeOptional($query)
    {
        return $query->where('type', 'optional');
    }
}
