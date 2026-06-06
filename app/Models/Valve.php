<?php

namespace App\Models;

use Database\Factories\ValveFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['valve_number', 'name', 'is_active', 'last_activated_at'])]
class Valve extends Model
{
    /** @use HasFactory<ValveFactory> */
    use HasFactory;

    protected $attributes = [
        'is_active' => false,
    ];

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'last_activated_at' => 'datetime',
        ];
    }
}
