<?php

namespace App\Models;

use Database\Factories\SystemSettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    /** @use HasFactory<SystemSettingFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'value',
    ];

    public static function value(string $key, ?string $default = null): ?string
    {
        return self::query()
            ->where('key', $key)
            ->value('value') ?? $default;
    }

    public static function floatValue(string $key, float $default): float
    {
        $value = self::value($key);

        return is_numeric($value) ? (float) $value : $default;
    }

    public static function boolValue(string $key, bool $default = false): bool
    {
        $value = self::value($key);

        if ($value === null) {
            return $default;
        }

        return in_array($value, ['1', 'true', 'yes', 'on'], true);
    }

    public static function put(string $key, string|int|float|bool|null $value): self
    {
        return self::query()->updateOrCreate(
            ['key' => $key],
            ['value' => (string) $value],
        );
    }
}
