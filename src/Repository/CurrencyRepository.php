<?php

namespace Kartikey\Core\Repository;

use Illuminate\Support\Facades\Event;
use Kartikey\Core\Models\Currency;
use Kartikey\Core\Eloquent\Repository;

class CurrencyRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return Currency::class;
    }
}
