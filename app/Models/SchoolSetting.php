<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/**
 * SchoolSetting — key/value store for school-wide configuration.
 *
 * Usage:
 *   SchoolSetting::get('school_name')           // returns value or null
 *   SchoolSetting::get('school_name', 'Default') // with fallback
 *   SchoolSetting::set('school_name', 'New Name')
 *   SchoolSetting::all()                         // returns key => value array
 *   SchoolSetting::logoUrl()                     // returns full URL or null
 *   SchoolSetting::logoBase64()                  // returns base64 for PDF embedding
 *
 * All reads are cached for 1 hour and flushed on any write.
 */
class SchoolSetting extends Model
{
    protected $table      = 'school_settings';
    protected $primaryKey = 'key';
    public    $incrementing = false;
    protected $keyType    = 'string';

    protected $fillable = ['key', 'value'];

    // ── Static helpers ────────────────────────────────────────────────────────

    public static function get(string $key, ?string $default = null): ?string
    {
        $all = static::allCached();
        return $all[$key] ?? $default;
    }

    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget('school_settings_all');
    }

    public static function setMany(array $data): void
    {
        foreach ($data as $key => $value) {
            static::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        Cache::forget('school_settings_all');
    }

    /**
     * Returns all settings as a flat key => value array.
     */
    public static function allCached(): array
    {
        return Cache::remember('school_settings_all', 3600, function () {
            return static::pluck('value', 'key')->toArray();
        });
    }

    /**
     * Returns the public URL for the school logo, or null if not set.
     */
    public static function logoUrl(): ?string
    {
        $path = static::get('school_logo');
        if (empty($path)) return null;
        return Storage::disk('public')->url($path);
    }

    /**
     * Returns the school logo as a base64 data URI for embedding in PDFs.
     * DomPDF cannot load URLs — it needs the image inline as base64.
     */
    public static function logoBase64(): ?string
    {
        $path = static::get('school_logo');
        if (empty($path)) return null;

        if (! Storage::disk('public')->exists($path)) return null;

        $contents = Storage::disk('public')->get($path);
        $mime     = Storage::disk('public')->mimeType($path);

        return 'data:' . $mime . ';base64,' . base64_encode($contents);
    }
}
