<?php

use App\Models\Schedule;
use App\Models\SystemSetting;
use App\Models\TelemetryLog;
use App\Models\Valve;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

afterEach(function () {
    CarbonImmutable::setTestNow();
});

function telemetryPayload(array $overrides = []): array
{
    return array_merge([
        'pressure_bar' => 3.4,
        'temperature_celsius' => 27.6,
        'humidity_percent' => 62.4,
        'inverter_hz' => 48.5,
        'inverter_status' => 'RUN',
        'inverter_current' => 6.2,
        'error_code' => 0,
    ], $overrides);
}

test('auto mode returns the first matching schedule and logs telemetry', function () {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-01 07:15:00', config('app.timezone')));

    $valveOne = Valve::factory()->create(['valve_number' => 1, 'name' => 'Lower Sector 1']);
    $valveTwo = Valve::factory()->create(['valve_number' => 2, 'name' => 'Lower Sector 2']);
    Valve::factory()->create(['valve_number' => 3, 'name' => 'Lower Sector 3']);
    Valve::factory()->create(['valve_number' => 4, 'name' => 'Lower Sector 4']);
    SystemSetting::factory()->create(['key' => 'system_mode', 'value' => 'auto']);

    Schedule::factory()->create([
        'valve_id' => $valveTwo->id,
        'days_of_week' => ['monday'],
        'start_time' => '07:00',
        'duration_minutes' => 30,
    ]);
    Schedule::factory()->create([
        'valve_id' => $valveOne->id,
        'days_of_week' => ['monday'],
        'start_time' => '07:10',
        'duration_minutes' => 30,
    ]);

    $this->postJson('/api/v1/irrigation/sync', telemetryPayload())
        ->assertSuccessful()
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('command', 'RUN_SEQUENCE')
        ->assertJsonPath('mode', 'auto')
        ->assertJsonPath('target_hz', 50)
        ->assertJsonPath('set_valves', [false, true, false, false])
        ->assertJsonPath('active_schedule.valve_number', 2);

    $telemetryLog = TelemetryLog::query()->first();

    expect(TelemetryLog::query()->count())->toBe(1)
        ->and($telemetryLog?->temperature_celsius)->toBe(27.6)
        ->and($telemetryLog?->humidity_percent)->toBe(62.4);
});

test('manual mode returns explicit valve states', function () {
    Valve::factory()->create(['valve_number' => 1, 'is_active' => true]);
    Valve::factory()->create(['valve_number' => 2, 'is_active' => false]);
    Valve::factory()->create(['valve_number' => 3, 'is_active' => true]);
    Valve::factory()->create(['valve_number' => 4, 'is_active' => false]);
    SystemSetting::factory()->create(['key' => 'system_mode', 'value' => 'manual']);

    $this->postJson('/api/v1/irrigation/sync', telemetryPayload())
        ->assertSuccessful()
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('command', 'RUN_SEQUENCE')
        ->assertJsonPath('mode', 'manual')
        ->assertJsonPath('target_hz', 45)
        ->assertJsonPath('set_valves', [true, false, true, false]);
});

test('manual mode uses the configured driver frequency and clamps it to fifty five hertz', function () {
    Valve::factory()->create(['valve_number' => 1, 'is_active' => true]);
    Valve::factory()->create(['valve_number' => 2, 'is_active' => false]);
    Valve::factory()->create(['valve_number' => 3, 'is_active' => false]);
    Valve::factory()->create(['valve_number' => 4, 'is_active' => false]);
    SystemSetting::factory()->create(['key' => 'system_mode', 'value' => 'manual']);
    SystemSetting::factory()->create(['key' => 'manual_target_hz', 'value' => '60']);

    $this->postJson('/api/v1/irrigation/sync', telemetryPayload())
        ->assertSuccessful()
        ->assertJsonPath('target_hz', 55)
        ->assertJsonPath('set_valves', [true, false, false, false]);
});

test('cycle schedules rotate active valves by day order', function () {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-03 07:15:00', config('app.timezone')));

    Valve::factory()->create(['valve_number' => 1, 'name' => 'Lower Sector 1']);
    Valve::factory()->create(['valve_number' => 2, 'name' => 'Lower Sector 2']);
    $valveThree = Valve::factory()->create(['valve_number' => 3, 'name' => 'Lower Sector 3']);
    Valve::factory()->create(['valve_number' => 4, 'name' => 'Lower Sector 4']);
    SystemSetting::factory()->create(['key' => 'system_mode', 'value' => 'auto']);

    Schedule::factory()->create([
        'valve_id' => $valveThree->id,
        'mode' => 'cycle',
        'days_of_week' => [],
        'cycle_valve_order' => [1, 2, 3, 4],
        'cycle_start_date' => '2026-06-01',
        'cycle_interval_days' => 1,
        'start_time' => '07:00',
        'duration_minutes' => 30,
        'target_hz' => 42.5,
    ]);

    $this->postJson('/api/v1/irrigation/sync', telemetryPayload())
        ->assertSuccessful()
        ->assertJsonPath('target_hz', 42.5)
        ->assertJsonPath('set_valves', [false, false, true, false])
        ->assertJsonPath('active_schedule.valve_number', 3);
});

test('unsafe pressure forces a stop regardless of mode', function () {
    Valve::factory()->create(['valve_number' => 1, 'is_active' => true]);
    Valve::factory()->create(['valve_number' => 2, 'is_active' => true]);
    Valve::factory()->create(['valve_number' => 3, 'is_active' => true]);
    Valve::factory()->create(['valve_number' => 4, 'is_active' => true]);
    SystemSetting::factory()->create(['key' => 'system_mode', 'value' => 'manual']);

    $this->postJson('/api/v1/irrigation/sync', telemetryPayload(['pressure_bar' => 4.1]))
        ->assertSuccessful()
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('command', 'FORCE_STOP')
        ->assertJsonPath('mode', 'safe-stop')
        ->assertJsonPath('target_hz', 0)
        ->assertJsonPath('set_valves', [false, false, false, false])
        ->assertJsonPath('safety.max_pressure_bar', 4)
        ->assertJsonPath('safety.pressure_shutdown', true)
        ->assertJsonPath('safety.shutdown_reason', 'pressure_limit');

    expect(Valve::query()->where('is_active', true)->count())->toBe(0)
        ->and(SystemSetting::boolValue('pressure_safety_tripped'))->toBeTrue()
        ->and(SystemSetting::floatValue('pressure_safety_pressure_bar', 0))->toBe(4.1);
});

test('emergency stop setting forces a stop regardless of telemetry', function () {
    Valve::factory()->create(['valve_number' => 1, 'is_active' => true]);
    Valve::factory()->create(['valve_number' => 2, 'is_active' => false]);
    Valve::factory()->create(['valve_number' => 3, 'is_active' => true]);
    Valve::factory()->create(['valve_number' => 4, 'is_active' => false]);
    SystemSetting::factory()->create(['key' => 'emergency_stop', 'value' => '1']);

    $this->postJson('/api/v1/irrigation/sync', telemetryPayload())
        ->assertSuccessful()
        ->assertJsonPath('command', 'FORCE_STOP')
        ->assertJsonPath('safety.emergency_stop', true)
        ->assertJsonPath('safety.pressure_shutdown', false)
        ->assertJsonPath('safety.shutdown_reason', 'emergency_stop')
        ->assertJsonPath('set_valves', [false, false, false, false]);
});
