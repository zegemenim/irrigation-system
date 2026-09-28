<?php

use App\Http\Controllers\Api\V1\IrrigationApiController;
use App\Http\Controllers\Api\V1\RelayApiController;
use App\Http\Controllers\Api\WindDataController;
use Illuminate\Support\Facades\Route;

Route::post('v1/irrigation/sync', IrrigationApiController::class)
    ->middleware('throttle:irrigation-device')
    ->name('api.v1.irrigation.sync');

Route::post('v1/relay/sync', RelayApiController::class)
    ->middleware('throttle:60,1')
    ->name('api.v1.relay.sync');

Route::post('wind-data', [WindDataController::class, 'store'])
    ->name('api.wind-data.store');

Route::get('chart-data', [WindDataController::class, 'chartData'])
    ->name('api.wind.chart-data');
