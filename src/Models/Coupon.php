<?php

namespace Kartikey\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kartikey\Core\Interface\Coupon as InterfaceCoupon;

class Coupon extends Model implements InterfaceCoupon
{
    use SoftDeletes;
    protected $table = COUPON_TABLE;
    protected $guarded = ['id'];

    protected $casts = [
        'rules' => 'array',
    ];
}
