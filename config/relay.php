<?php

return [
    'device_token' => env('RELAY_DEVICE_TOKEN'),
    'relay_count' => (int) env('RELAY_COUNT', 8),
    'sensor_count' => (int) env('RELAY_SENSOR_COUNT', 2),
];
