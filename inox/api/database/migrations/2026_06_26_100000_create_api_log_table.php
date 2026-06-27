<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('api_log', function (Blueprint $table) {
            $table->id();
            $table->string('method', 10);
            $table->string('url', 500);
            $table->unsignedSmallInteger('status_code');
            $table->string('ip_address', 45)->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->text('request_headers')->nullable();
            $table->longText('request_body')->nullable();
            $table->longText('response_body')->nullable();
            $table->timestamps();

            $table->index('method');
            $table->index('status_code');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_log');
    }
};
