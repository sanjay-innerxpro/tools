<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScanAsset extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'scan_id',
        'url',
        'filename',
        'type',
        'mime_type',
        'extension',
        'file_size',
        'quality',
        'quality_variants',
        'thumbnail_url',
        'is_drm',
        'is_downloadable',
        'source',
        'metadata',
    ];

    protected $casts = [
        'quality_variants' => 'array',
        'metadata' => 'array',
        'is_drm' => 'boolean',
        'is_downloadable' => 'boolean',
        'file_size' => 'integer',
    ];

    public function scan(): BelongsTo
    {
        return $this->belongsTo(Scan::class);
    }

    public function getSizeFormattedAttribute(): ?string
    {
        if ($this->file_size === null) {
            return null;
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;

        foreach ($units as $unit) {
            if ($size < 1024) {
                return round($size, 1) . ' ' . $unit;
            }
            $size /= 1024;
        }

        return round($size, 1) . ' TB';
    }
}
