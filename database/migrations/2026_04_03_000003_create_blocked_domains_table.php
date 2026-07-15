<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blocked_domains', function (Blueprint $table) {
            $table->id();
            $table->string('domain', 255)->unique();
            $table->string('reason', 500)->nullable();
            $table->timestamp('blocked_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blocked_domains');
    }
};
