<?php

use App\Models\WindLog;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

afterEach(function () {
    CarbonImmutable::setTestNow();
});

test('wind data endpoint stores wind speed with generated power', function () {
    $this->postJson('/api/wind-data', ['wind_speed' => 4.5])
        ->assertCreated()
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('data.wind_speed', 4.5)
        ->assertJsonPath('data.generated_power', 4.67);

    $windLog = WindLog::query()->first();

    expect(WindLog::query()->count())->toBe(1)
        ->and($windLog?->wind_speed)->toBe(4.5)
        ->and(round($windLog?->generated_power ?? 0.0, 2))->toBe(4.67);
});

test('wind data endpoint clamps generated power by turbine curve', function (float $windSpeed, float $expectedPower) {
    $response = $this->postJson('/api/wind-data', ['wind_speed' => $windSpeed])
        ->assertCreated();

    expect((float) $response->json('data.generated_power'))->toBe($expectedPower);
})->with([
    'below cut in' => [2.4, 0.0],
    'at cut in' => [2.5, 0.0],
    'rated wind' => [12.0, 500.0],
    'above rated wind' => [13.2, 500.0],
]);

test('chart data endpoint returns the latest thirty readings in chronological order', function () {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-07-05 12:00:00', config('app.timezone')));

    for ($index = 1; $index <= 35; $index++) {
        WindLog::factory()->create([
            'wind_speed' => $index,
            'generated_power' => $index * 10,
            'created_at' => now()->addSeconds($index),
            'updated_at' => now()->addSeconds($index),
        ]);
    }

    $response = $this->getJson('/api/chart-data')
        ->assertSuccessful()
        ->assertJsonCount(30, 'data');

    expect($response->json('data.0.wind_speed'))->toBe(6)
        ->and($response->json('data.29.wind_speed'))->toBe(35);
});

test('home route renders wind dashboard', function () {
    $this->get('/')
        ->assertSuccessful()
        ->assertSee('Istabreeze 500W', false)
        ->assertSee('chart-data', false);
});
