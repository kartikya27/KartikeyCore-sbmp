<?php

namespace Kartikey\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kartikey\Core\Interface\Channel as InterfaceChannel;

class Channel extends Model implements InterfaceChannel
{
    use SoftDeletes;
    protected $table = CHANNEL_TABLE;
    protected $guarded = ['id'];
}
