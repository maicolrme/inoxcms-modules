<?php

return [
    'auth_type' => env('API_AUTH_TYPE', 'sanctum'),
    'rate_limit' => (int) env('API_RATE_LIMIT', 60),
    'log_enabled' => (bool) env('API_LOG_ENABLED', true),
];
