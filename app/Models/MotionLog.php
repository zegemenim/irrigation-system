<?php

namespace App\Models;

use Database\Factories\MotionLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MotionLog extends Model
{
    /** @use HasFactory<MotionLogFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'sensor_id',
        'motion_detected',
        'triggered_relay_ids',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'motion_detected' => 'boolean',
            'triggered_relay_ids' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Sensor, $this>
     */
    public function sensor(): BelongsTo
    {
        return $this->belongsTo(Sensor::class);
    }
}
