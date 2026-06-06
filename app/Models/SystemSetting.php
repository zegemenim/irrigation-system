<?php

namespace App\Models;

use Database\Factories\SystemSettingFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['key', 'value'])]
class SystemSetting extends Model
{
    /** @use HasFactory<SystemSettingFactory> */
    use HasFactory;

    public static function value(string $key, ?string $default = null): ?string
    {
        return self::query()
            ->where('key', $key)
            ->value('value') ?? $default;
    }
}
