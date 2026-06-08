<?php

namespace App\Models;

use Database\Factories\ValveFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Valve extends Model
{
    /** @use HasFactory<ValveFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'valve_number',
        'name',
        'is_active',
        'last_activated_at',
    ];

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
