<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /** ذاكرة داخل الطلب: تمنع قراءة الكاش لكل صف في الجداول */
    protected static array $memo = [];

    public static function get(string $key, $default = null)
    {
        if (array_key_exists($key, static::$memo)) {
            return static::$memo[$key];
        }

        return static::$memo[$key] = Cache::rememberForever(
            "setting.$key",
            fn () => static::where('key', $key)->value('value') ?? $default
        );
    }

    /** تحميل كل الإعدادات دفعةً واحدة (استعلام واحد) */
    public static function preload(): void
    {
        $all = Cache::rememberForever('settings.all', fn () => static::pluck('value', 'key')->all());
        static::$memo = array_merge($all, static::$memo);
    }

    public static function set(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        unset(static::$memo[$key]);
        Cache::forget("setting.$key");
        Cache::forget('settings.all');
    }
}
