<?php

return [
    'device_token' => env('IRRIGATION_DEVICE_TOKEN'),
    'max_safe_pressure_bar' => (float) env('IRRIGATION_MAX_SAFE_PRESSURE_BAR', 6.0),
    'default_target_hz' => (float) env('IRRIGATION_DEFAULT_TARGET_HZ', 50.0),
];
