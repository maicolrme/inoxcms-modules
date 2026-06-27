<?php

return [
    'schema_path' => dirname(__DIR__, 4) . '/schema',
    'models_path' => app_path('Models'),
    'policies_path' => app_path('Policies'),
    'migrations_path' => database_path('migrations'),
];
