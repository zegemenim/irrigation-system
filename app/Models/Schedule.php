<?php

namespace App\Models;

use Database\Factories\ScheduleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['valve_id', 'days_of_week', 'start_time', 'duration_minutes', 'is_enabled'])]
class Schedule extends Model
{
    /** @use HasFactory<ScheduleFactory> */
    use HasFactory;

    protected $attributes = [
        'is_enabled' => true,
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
            'is_enabled' => 'boolean',
            'start_time' => 'datetime:H:i',
        ];
    }
}
