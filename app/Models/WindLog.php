<?php

namespace App\Models;

use Database\Factories\WindLogFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WindLog extends Model
{
    /** @use HasFactory<WindLogFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'wind_speed',
        'generated_power',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'wind_speed' => 'float',
            'generated_power' => 'float',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
