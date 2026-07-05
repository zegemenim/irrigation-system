<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guest users are redirected from the root dashboard to the login screen', function () {
    $response = $this->get('/');

    $response->assertRedirect('/admin/login');
});

test('dashboard path redirects to the root dashboard', function () {
    $response = $this->get('/dashboard');

    $response->assertRedirect('/');
});

test('authenticated users open the irrigation application at the root path', function () {
    $this->actingAs(User::factory()->create());

    $this->followingRedirects()
        ->get('/')
        ->assertSuccessful()
        ->assertSee('4 bölme, tek kontrol yüzeyi');
});
