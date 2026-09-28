<?php

namespace App\Models;

use Database\Factories\SensorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sensor extends Model
{
    /** @use HasFactory<SensorFactory> */
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'sensor_number',
        'name',
    ];

    /**
     * @return BelongsToMany<Relay, $this>
     */
    public function relays(): BelongsToMany
    {
        return $this->belongsToMany(Relay::class, 'sensor_relay');
    }

    /**
     * @return HasMany<MotionLog, $this>
     */
    public function motionLogs(): HasMany
    {
        return $this->hasMany(MotionLog::class);
    }
}
