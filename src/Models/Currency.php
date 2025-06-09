<?php

namespace Kartikey\Core\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Kartikey\Core\Interface\Currency as CurrencyInterface;
use Kartikey\Core\Database\Factories\CurrencyFactory;
use Kartikey\Core\Factories\CurrencyFactory as FactoriesCurrencyFactory;

class Currency extends Model implements CurrencyInterface
{
    use HasFactory;
    protected $table = CURRENCY_TABLE;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'name',
        'symbol',
        'decimal',
        'group_separator',
        'decimal_separator',
        'currency_position',
    ];

    /**
     * Set currency code in capital letter.
     */
    public function setCodeAttribute($code): void
    {
        $this->attributes['code'] = strtoupper($code);
    }

    /**
     * Get the exchange rate associated with the currency.
     */
    public function exchangeRate(): HasOne
    {
        // Directly reference the related model instead of using a proxy
        return $this->hasOne(CurrencyExchangeRate::class, 'target_currency');
    }

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory(): Factory
    {
        return FactoriesCurrencyFactory::new();
    }
}
