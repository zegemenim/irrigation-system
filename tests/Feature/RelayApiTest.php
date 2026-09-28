<?php

use App\Models\Relay;
use App\Models\Sensor;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config(['relay.device_token' => 'test-relay-token']);

    // Create default relays (8 total)
    foreach (range(1, 8) as $n) {
        Relay::factory()->create(['relay_number' => $n]);
    }

    // Create 2 sensors with explicit numbers
    Sensor::factory()->create(['sensor_number' => 1, 'name' => 'Hareket Sensörü 1']);
    Sensor::factory()->create(['sensor_number' => 2, 'name' => 'Hareket Sensörü 2']);
});

it('rejects requests without a device token', function () {
    $this->postJson(route('api.v1.relay.sync'), [
        'sensors' => [['sensor_number' => 1, 'motion_detected' => false]],
    ])->assertForbidden();
});

it('rejects requests with an invalid device token', function () {
    $this->postJson(route('api.v1.relay.sync'), [
        'sensors' => [['sensor_number' => 1, 'motion_detected' => false]],
    ], ['X-Device-Token' => 'wrong-token'])->assertForbidden();
});

it('accepts valid sync request and returns relay states', function () {
    $response = $this->postJson(route('api.v1.relay.sync'), [
        'sensors' => [
            ['sensor_number' => 1, 'motion_detected' => false],
            ['sensor_number' => 2, 'motion_detected' => false],
        ],
    ], ['X-Device-Token' => 'test-relay-token']);

    $response->assertOk()
        ->assertJsonStructure(['status', 'set_relays'])
        ->assertJsonPath('status', 'success');

    expect($response->json('set_relays'))->toBeArray()->toHaveCount(8);
});

it('activates mapped relays when motion is detected', function () {
    $relay1 = Relay::where('relay_number', 1)->first();
    $relay2 = Relay::where('relay_number', 2)->first();
    $sensor1 = Sensor::where('sensor_number', 1)->first();

    // Assign relay 1 and 2 to sensor 1
    $sensor1->relays()->attach([$relay1->id, $relay2->id]);

    $this->postJson(route('api.v1.relay.sync'), [
        'sensors' => [['sensor_number' => 1, 'motion_detected' => true]],
    ], ['X-Device-Token' => 'test-relay-token'])->assertOk();

    expect($relay1->fresh()->is_active)->toBeTrue();
    expect($relay2->fresh()->is_active)->toBeTrue();
    // Unrelated relay should stay off
    expect(Relay::where('relay_number', 3)->first()->is_active)->toBeFalse();
});

it('logs motion events to motion_logs table', function () {
    $this->postJson(route('api.v1.relay.sync'), [
        'sensors' => [
            ['sensor_number' => 1, 'motion_detected' => true],
            ['sensor_number' => 2, 'motion_detected' => false],
        ],
    ], ['X-Device-Token' => 'test-relay-token'])->assertOk();

    $this->assertDatabaseCount('motion_logs', 2);
    $this->assertDatabaseHas('motion_logs', ['motion_detected' => true]);
    $this->assertDatabaseHas('motion_logs', ['motion_detected' => false]);
});

it('validates that sensor_number is required', function () {
    $this->postJson(route('api.v1.relay.sync'), [
        'sensors' => [['motion_detected' => true]],
    ], ['X-Device-Token' => 'test-relay-token'])->assertUnprocessable();
});
