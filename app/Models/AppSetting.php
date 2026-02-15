<?php

namespace App\Models;

use App\Traits\Loggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AppSetting extends Model
{
    use Loggable;
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'description',
    ];

    private static string $cachePrefix = 'app_setting_';
    private static int $cacheTtl = 3600; // 1 hour

    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember(self::$cachePrefix . $key, self::$cacheTtl, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();

            if (!$setting) {
                return $default;
            }

            return self::castValue($setting->value, $setting->type);
        });
    }

    public static function set(string $key, mixed $value): bool
    {
        $setting = static::where('key', $key)->first();

        if (!$setting) {
            return false;
        }

        $stringValue = self::valueToString($value, $setting->type);
        $setting->value = $stringValue;
        $setting->save();

        Cache::forget(self::$cachePrefix . $key);

        return true;
    }

    public static function clearCache(): void
    {
        $settings = static::all();
        foreach ($settings as $setting) {
            Cache::forget(self::$cachePrefix . $setting->key);
        }
    }

    public function getCastedValueAttribute(): mixed
    {
        return self::castValue($this->value, $this->type);
    }

    private static function castValue(?string $value, string $type): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'integer' => (int) $value,
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($value, true),
            default => $value,
        };
    }

    private static function valueToString(mixed $value, string $type): string
    {
        return match ($type) {
            'boolean' => $value ? 'true' : 'false',
            'json' => json_encode($value),
            default => (string) $value,
        };
    }
}
