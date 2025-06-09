<?php

namespace Kartikey\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UnifiedBuffer extends Model
{
    use SoftDeletes;
    protected $table = UNIFIED_BUFFER_TABLE;

    protected $casts = [
        'data' => 'array'
    ];

    protected $fillable = [
        'type',
        'data',
        'status',
    ];
}
