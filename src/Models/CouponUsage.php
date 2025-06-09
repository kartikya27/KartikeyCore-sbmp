<?php

namespace Kartikey\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class CouponUsage extends Model
{
    use SoftDeletes;
    protected $table = COUPON_USE_TABLE;
    protected $guarded = ['id'];
}
