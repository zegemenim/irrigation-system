<?php

namespace App\Models;

use Database\Factories\ScheduleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Schedule extends Model
{
    /** @use HasFactory<ScheduleFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'valve_id',
        'mode',
        'days_of_week',
        'cycle_valve_order',
        'cycle_start_date',
        'cycle_interval_days',
        'start_time',
        'duration_minutes',
        'target_hz',
        'is_enabled',
    ];

    protected $attributes = [
        'mode' => 'weekly',
        'is_enabled' => true,
        'cycle_interval_days' => 1,
    ];

    public function valve(): BelongsTo
    {
        return $this->belongsTo(Valve::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'days_of_week' => 'array',
            'cycle_valve_order' => 'array',
            'cycle_start_date' => 'date',
            'cycle_interval_days' => 'integer',
            'target_hz' => 'float',
            'is_enabled' => 'boolean',
            'start_time' => 'datetime:H:i',
        ];
    }
}
