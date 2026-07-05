<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guest users can open the public wind dashboard at the root path', function () {
    $this->get('/')
        ->assertSuccessful()
        ->assertSee('Istabreeze 500W', false);
});

test('guest users are redirected from dashboard to the login screen', function () {
    $response = $this->get('/dashboard');

    $response->assertRedirect('/admin/login');
});

test('authenticated users open the irrigation application at the dashboard path', function () {
    $this->actingAs(User::factory()->create());

    $this->followingRedirects()
        ->get('/dashboard')
        ->assertSuccessful()
        ->assertSee('4 bölme, tek kontrol yüzeyi');
});
