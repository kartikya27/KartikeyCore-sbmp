<?php

namespace Kartikey\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kartikey\Core\Database\Factories\CoreConfigFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CoreConfig extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = CORE_CONFIG_TABLE;
    protected $guarded = ['id'];
    protected $hidden = ['token'];

     /**
     * Create a new factory instance for the model
     */
    protected static function newFactory(): Factory
    {
        return CoreConfigFactory::new();
    }
}
