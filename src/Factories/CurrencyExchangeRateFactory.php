<?php

namespace Kartikey\Core\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Kartikey\Core\Models\CurrencyExchangeRate;

class CurrencyExchangeRateFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = CurrencyExchangeRate::class;

    /**
     * Define the model's default state.
     */
    public function definition()
    {
        return [
            'rate' => rand(1, 100),
        ];
    }
}
