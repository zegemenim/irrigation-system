<?php

namespace App\Models;

use Database\Factories\RelayFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Relay extends Model
{
    /** @use HasFactory<RelayFactory> */
    use HasFactory;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'relay_number',
        'name',
        'is_active',
        'last_activated_at',
    ];

    protected $attributes = [
        'is_active' => false,
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_activated_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsToMany<Sensor, $this>
     */
    public function sensors(): BelongsToMany
    {
        return $this->belongsToMany(Sensor::class, 'sensor_relay');
    }
}
