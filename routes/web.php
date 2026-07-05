<?php

use App\Livewire\IrrigationDashboard;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('wind-dashboard');
})->name('home');

Route::redirect('/login', '/admin/login')->name('login');

Route::get('/dashboard', IrrigationDashboard::class)
    ->middleware('auth')
    ->name('dashboard');
