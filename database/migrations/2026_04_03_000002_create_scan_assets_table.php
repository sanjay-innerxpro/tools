<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scan_assets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('scan_id');
            $table->text('url');
            $table->string('filename', 500)->nullable();
            $table->enum('type', ['video', 'audio', 'document', 'image', 'other']);
            $table->string('mime_type', 100)->nullable();
            $table->string('extension', 20)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('quality', 20)->nullable();
            $table->json('quality_variants')->nullable();
            $table->text('thumbnail_url')->nullable();
            $table->boolean('is_drm')->default(false);
            $table->boolean('is_downloadable')->default(true);
            $table->string('source', 50)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->foreign('scan_id')->references('id')->on('scans')->onDelete('cascade');
            $table->index(['scan_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scan_assets');
    }
};
