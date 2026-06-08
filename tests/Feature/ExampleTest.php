<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guest users are redirected from the root application to the login screen', function () {
    $response = $this->get('/');

    $response->assertRedirect('/admin/login');
});

test('guest users are redirected from dashboard to the login screen', function () {
    $response = $this->get('/dashboard');

    $response->assertRedirect('/admin/login');
});

test('authenticated users open the irrigation application at the root path', function () {
    $this->actingAs(User::factory()->create());

    $this->followingRedirects()
        ->get('/')
        ->assertSuccessful()
        ->assertSee('4 bölme, tek kontrol yüzeyi');
});
