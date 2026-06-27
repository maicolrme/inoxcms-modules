<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('original_name');
            $table->string('path');
            $table->string('disk', 50);
            $table->string('mime_type', 100);
            $table->unsignedInteger('size');
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('alt_text')->nullable();
            $table->string('caption')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('disk');
        });

        Schema::create('mediables', function (Blueprint $table) {
            $table->foreignId('media_id')->constrained()->cascadeOnDelete();
            $table->morphs('mediable');
            $table->string('role')->default('default');

            $table->primary(['media_id', 'mediable_id', 'mediable_type', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mediables');
        Schema::dropIfExists('media');
    }
};
