<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('url', 2048);
            $table->string('resolved_url', 2048)->nullable();
            $table->string('page_title', 500)->nullable();
            $table->enum('status', ['queued', 'processing', 'completed', 'failed'])->default('queued');
            $table->string('error_code', 50)->nullable();
            $table->text('error_message')->nullable();
            $table->unsignedInteger('asset_count')->default(0);
            $table->string('ip_address', 45);
            $table->string('user_agent', 500)->nullable();
            $table->json('options')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->timestamp('expires_at')->nullable();

            $table->index(['ip_address', 'created_at']);
            $table->index('status');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scans');
    }
};
