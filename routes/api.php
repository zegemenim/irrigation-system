<?php

use App\Http\Controllers\Api\V1\IrrigationApiController;
use Illuminate\Support\Facades\Route;

Route::post('v1/irrigation/sync', IrrigationApiController::class)
    ->middleware('throttle:irrigation-device')
    ->name('api.v1.irrigation.sync');
