<?php

namespace Kartikey\Core\Database\Seeders;

use Illuminate\Database\Seeder;
use Kartikey\Core\Models\Channel;
use Kartikey\Core\Models\Currency;
use Kartikey\Core\Models\Locale;

class ChannelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $local = Locale::create(
            [
                'code' => 'de',
                'name' => 'german',
                'direction' => 'ltr',
            ]
        );

        $currecny = Currency::create(
            [
                'code' => 'EUR',
                'name' => 'EURO',
                'symbol' => '€',
                'decimal' => 2,
            ]
        );

        Channel::create(
            [
                'code' => 'default',
                'hostname' => env('APP_URL'),
                'default_locale_id' => $local->id,
                'base_currency_id' => $currecny->id,
            ]
        );
    }
}
