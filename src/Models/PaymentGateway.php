<?php

namespace Kartikey\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Kartikey\Payment\Interface\PaymentInterface;

class PaymentGateway extends Model implements PaymentInterface
{
    use SoftDeletes;
    protected $table = PAYMENT_GATEWAY_TABLE;

    protected $fillable = [
        'method',
        'app_name',
        'app_id',
        'secret',
        'key',
        'success_url',
        'cancel_url',
        'mode',
        'status',
    ];

    // Accessor to append app_id as a class attribute
    public function getAppIdClassAttribute()
    {
        return $this->app_id;
    }

    protected $appends = ['app_id_class'];
}
