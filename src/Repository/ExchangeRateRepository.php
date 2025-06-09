<?php

namespace Kartikey\Core\Repository;

use Kartikey\Core\Eloquent\Repository;

class ExchangeRateRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return 'Kartikey\Core\Models\CurrencyExchangeRate';
    }
}
