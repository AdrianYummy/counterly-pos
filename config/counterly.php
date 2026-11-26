<?php

return [
    'display_timezone' => env('DISPLAY_TIMEZONE', 'Asia/Manila'),
    'low_stock_threshold' => (int) env('LOW_STOCK_THRESHOLD', 5),
    'demo_password' => env('DEMO_STAFF_PASSWORD'),
    'mail_preview' => env('MAIL_PREVIEW_LINK', false),
    'session_idle_timeout' => (int) env('SESSION_IDLE_TIMEOUT', 900),
    'session_heartbeat_timeout' => (int) env('SESSION_HEARTBEAT_TIMEOUT', 180),
    'session_close_grace' => (int) env('SESSION_CLOSE_GRACE', 10),
];
