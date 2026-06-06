<?php

namespace App\Models;

use Database\Factories\TelemetryLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['pressure_bar', 'inverter_hz', 'inverter_status', 'inverter_current', 'error_code'])]
class TelemetryLog extends Model
{
    /** @use HasFactory<TelemetryLogFactory> */
    use HasFactory;

    public const null UPDATED_AT = null;

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
            'inverter_hz' => 'float',
            'inverter_current' => 'float',
            'error_code' => 'integer',
            'created_at' => 'datetime',
        ];
    }
}
