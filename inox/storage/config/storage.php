<?php

return [
    'disk' => env('STORAGE_DISK', 'local'),

    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/public/storage'),
            'url' => rtrim(env('APP_URL', 'http://localhost'), '/') . '/storage/media',
            'visibility' => 'public',
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('STORAGE_AWS_ACCESS_KEY_ID'),
            'secret' => env('STORAGE_AWS_SECRET_ACCESS_KEY'),
            'region' => env('STORAGE_AWS_DEFAULT_REGION', 'us-east-1'),
            'bucket' => env('STORAGE_AWS_BUCKET'),
            'url' => env('STORAGE_AWS_URL'),
            'endpoint' => env('STORAGE_AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('STORAGE_AWS_USE_PATH_STYLE_ENDPOINT', false),
        ],

        'r2' => [
            'driver' => 's3',
            'key' => env('STORAGE_R2_ACCESS_KEY_ID'),
            'secret' => env('STORAGE_R2_SECRET_ACCESS_KEY'),
            'region' => env('STORAGE_R2_REGION', 'auto'),
            'bucket' => env('STORAGE_R2_BUCKET'),
            'url' => env('STORAGE_R2_URL'),
            'endpoint' => env('STORAGE_R2_ENDPOINT'),
            'use_path_style_endpoint' => env('STORAGE_R2_USE_PATH_STYLE_ENDPOINT', true),
        ],
    ],

    'allowed_mimes' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'avif'],
    'max_file_size' => 10 * 1024, // KB
];
