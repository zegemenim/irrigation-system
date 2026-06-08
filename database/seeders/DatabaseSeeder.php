<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use App\Models\User;
use App\Models\Valve;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'zegemenim@gmail.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'is_admin' => true,
            ],
        );

        foreach ([
            1 => '1. Bölme - En Alt Bölme',
            2 => '2. Bölme',
            3 => '3. Bölme',
            4 => '4. Bölme - En Üst Bölme',
        ] as $valveNumber => $valveName) {
            Valve::query()->updateOrCreate(
                ['valve_number' => $valveNumber],
                [
                    'name' => $valveName,
                    'is_active' => false,
                ],
            );
        }

        SystemSetting::query()->updateOrCreate(
            ['key' => 'system_mode'],
            ['value' => 'auto'],
        );

        SystemSetting::query()->updateOrCreate(
            ['key' => 'emergency_stop'],
            ['value' => '0'],
        );

        SystemSetting::query()->updateOrCreate(
            ['key' => 'max_safe_pressure_bar'],
            ['value' => (string) config('irrigation.max_safe_pressure_bar')],
        );

        SystemSetting::query()->updateOrCreate(
            ['key' => 'default_target_hz'],
            ['value' => (string) config('irrigation.default_target_hz')],
        );

        SystemSetting::query()->updateOrCreate(
            ['key' => 'manual_target_hz'],
            ['value' => (string) config('irrigation.manual_target_hz')],
        );
    }
}
