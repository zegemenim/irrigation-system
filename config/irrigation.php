<?php

return [
    'device_token' => env('IRRIGATION_DEVICE_TOKEN'),
    'valve_count' => (int) env('IRRIGATION_VALVE_COUNT', 4),
    'max_safe_pressure_bar' => (float) env('IRRIGATION_MAX_SAFE_PRESSURE_BAR', 4.0),
    'default_target_hz' => (float) env('IRRIGATION_DEFAULT_TARGET_HZ', 50.0),
    'manual_target_hz' => (float) env('IRRIGATION_MANUAL_TARGET_HZ', 45.0),
    'max_target_hz' => (float) env('IRRIGATION_MAX_TARGET_HZ', 55.0),
];
