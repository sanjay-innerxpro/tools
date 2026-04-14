<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlockedDomain extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'domain',
        'reason',
        'blocked_at',
    ];

    protected $casts = [
        'blocked_at' => 'datetime',
    ];
}
