<?php

use App\Livewire\IrrigationDashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return redirect()->route('filament.admin.auth.login');
})->name('home');

Route::redirect('/login', '/admin/login')->name('login');

Route::get('/dashboard', IrrigationDashboard::class)
    ->middleware('auth')
    ->name('dashboard');
