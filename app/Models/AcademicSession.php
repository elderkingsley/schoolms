<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicSession extends Model
{
    protected $fillable = ['name', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function terms(): HasMany
    {
        return $this->hasMany(Term::class);
    }

    public function activeTerm(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Term::class)->where('is_active', true);
    }

    public static function current(): ?self
    {
        return self::where('is_active', true)->first();
    }
}
