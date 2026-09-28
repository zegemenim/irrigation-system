<?php

use App\Livewire\RelayDashboard;
use App\Models\Relay;
use App\Models\Sensor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('redirects guests to login', function () {
    $this->get(route('relay'))->assertRedirect('/admin/login');
});

it('renders relay dashboard for authenticated user', function () {
    $this->actingAs($this->user)
        ->get(route('relay'))
        ->assertOk();
});

it('creates default relay and sensor records on mount', function () {
    $this->actingAs($this->user);

    Livewire::test(RelayDashboard::class);

    expect(Relay::count())->toBe(8);
    expect(Sensor::count())->toBe(2);
});

it('shows existing relays on dashboard', function () {
    $relay = Relay::factory()->create(['name' => 'Test Projektör', 'relay_number' => 1]);

    $this->actingAs($this->user);

    Livewire::test(RelayDashboard::class)
        ->assertSee($relay->name);
});

it('toggles a relay on', function () {
    $relay = Relay::factory()->create(['is_active' => false, 'relay_number' => 1]);

    $this->actingAs($this->user);

    Livewire::test(RelayDashboard::class)
        ->call('toggleRelay', $relay->id);

    expect($relay->fresh()->is_active)->toBeTrue();
});

it('toggles a relay off', function () {
    $relay = Relay::factory()->active()->create(['relay_number' => 1]);

    $this->actingAs($this->user);

    Livewire::test(RelayDashboard::class)
        ->call('toggleRelay', $relay->id);

    expect($relay->fresh()->is_active)->toBeFalse();
});

it('turns all relays on', function () {
    foreach (range(1, 3) as $n) {
        Relay::factory()->create(['relay_number' => $n]);
    }

    $this->actingAs($this->user);

    Livewire::test(RelayDashboard::class)
        ->call('allOn');

    expect(Relay::where('is_active', true)->count())->toBe(Relay::count());
});

it('turns all relays off', function () {
    foreach (range(1, 3) as $n) {
        Relay::factory()->active()->create(['relay_number' => $n]);
    }

    $this->actingAs($this->user);

    Livewire::test(RelayDashboard::class)
        ->call('allOff');

    expect(Relay::where('is_active', true)->count())->toBe(0);
});
