<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Scan extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'url',
        'resolved_url',
        'page_title',
        'status',
        'error_code',
        'error_message',
        'asset_count',
        'ip_address',
        'user_agent',
        'options',
        'started_at',
        'completed_at',
        'expires_at',
    ];

    protected $casts = [
        'options' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function assets(): HasMany
    {
        return $this->hasMany(ScanAsset::class);
    }

    public function markProcessing(): void
    {
        $this->update([
            'status' => 'processing',
            'started_at' => now(),
        ]);
    }

    public function markCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'asset_count' => $this->assets()->count(),
            'completed_at' => now(),
        ]);
    }

    public function markFailed(string $code, string $message): void
    {
        $this->update([
            'status' => 'failed',
            'error_code' => $code,
            'error_message' => $message,
            'completed_at' => now(),
        ]);
    }
}
