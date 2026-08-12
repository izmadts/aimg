<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, $default = null)
    {
        return Cache::rememberForever("setting.{$key}", function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    public static function set(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("setting.{$key}");
    }

    /**
     * Remove a setting entirely so get() falls back to its $default again -
     * used instead of set($key, '') for optional text fields, so saving the
     * settings form with a field left blank doesn't permanently pin an empty
     * string over whatever default the app would otherwise show.
     */
    public static function forget(string $key): void
    {
        static::where('key', $key)->delete();
        Cache::forget("setting.{$key}");
    }

    /**
     * Set $key to $value, or forget() it if $value is blank.
     */
    public static function setOrForget(string $key, ?string $value): void
    {
        $value = trim((string) $value);
        if ($value === '') {
            static::forget($key);
        } else {
            static::set($key, $value);
        }
    }

    public static function bool(string $key, bool $default = false): bool
    {
        $value = static::get($key, $default ? '1' : '0');
        return in_array($value, [true, 1, '1', 'true'], true);
    }

    /**
     * Public URL for a setting that stores a public-disk file path (e.g. company_logo, favicon).
     */
    public static function url(string $key, $default = null)
    {
        $path = static::get($key);
        return $path ? \Illuminate\Support\Facades\Storage::disk('public')->url($path) : $default;
    }
}
