<?php

use App\Livewire\IrrigationDashboard;
use Illuminate\Support\Facades\Route;

Route::get('/wind', function () {
    return view('wind-dashboard');
})->name('wind');

Route::redirect('/login', '/admin/login')->name('login');

Route::get('/', IrrigationDashboard::class)
    ->middleware('auth')
    ->name('dashboard');

Route::redirect('/dashboard', '/');
