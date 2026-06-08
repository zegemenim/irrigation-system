<?php

namespace App\Models;

use Database\Factories\TelemetryLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TelemetryLog extends Model
{
    /** @use HasFactory<TelemetryLogFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'pressure_bar',
        'temperature_celsius',
        'humidity_percent',
        'inverter_hz',
        'inverter_status',
        'inverter_current',
        'error_code',
    ];

    protected $attributes = [
        'error_code' => 0,
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'pressure_bar' => 'float',
            'temperature_celsius' => 'float',
            'humidity_percent' => 'float',
            'inverter_hz' => 'float',
            'inverter_current' => 'float',
            'error_code' => 'integer',
            'created_at' => 'datetime',
        ];
    }
}
