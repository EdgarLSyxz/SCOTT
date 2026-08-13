<?php

return [
    'rain_notification' => [
        'recipients' => array_values(array_filter(array_map(
            'trim',
            explode(',', env('WEATHER_RAIN_NOTIFY_EMAILS', 'ecuevas@stargroup.com.mx'))
        ))),
        'threshold_mm' => (float) env('WEATHER_RAIN_THRESHOLD_MM', 0.1),
        'cooldown_minutes' => (int) env('WEATHER_RAIN_COOLDOWN_MINUTES', 15),
        'enabled' => (bool) env('WEATHER_RAIN_NOTIFICATIONS_ENABLED', true),
    ],
];