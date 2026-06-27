<?php

namespace Inox\Api\Models;

use Illuminate\Database\Eloquent\Model;

class ApiLogEntry extends Model
{
    protected $table = 'api_log';

    protected $fillable = [
        'method', 'url', 'status_code', 'ip_address', 'user_id',
        'duration_ms', 'request_headers', 'request_body', 'response_body',
    ];

    protected $casts = [
        'request_headers' => 'array',
        'created_at' => 'datetime',
    ];
}
