<?php

namespace Kartikey\Core;

use Stegback\Category\Repository\CategoryRepository;
use Kartikey\Core\Repository\CurrencyRepository;
use Kartikey\Core\Concerns\CurrencyFormatter;
use Kartikey\Core\Repository\ExchangeRateRepository;
use Stegback\Seller\Repository\BrandRepository;
use Stegback\Seller\Repository\SellerRepository;
use Stegback\Seller\Repository\CompanyRepository;
use Kartikey\Core\Models\Currency as CurrencyModel;
use Kartikey\Core\Interface\Currency;
use Carbon\Carbon;
use Stegback\Checkout\Models\CartAddress;
use Kartikey\Core\Interface\Channel;
use Kartikey\Core\Repository\ChannelRepository;
use Kartikey\Core\Repository\LocaleRepository;

class Core
{
    use CurrencyFormatter;

        /**
     * Default Channel.
     *
     * @var \Kartikey\Core\Models\Channel
     */
    protected $defaultChannel;

    /**
     * Currency.
     *
     * @var \Kartikey\Core\Models\Currency
     */
    protected $currentCurrency;

    /**
     * Exchange rates
     *
     * @var array
     */
    protected $exchangeRates = [];
    const DEFAULT_CURRENCY = "EUR";

    /**
     * Current Channel.
     *
     * @var \Kartikey\Core\Models\Channel
     */
    protected $currentChannel;

    /**
     * Create a new instance.
     *
     * @return void
     */
    public function __construct(
        protected CurrencyRepository $currencyRepository,
        protected ChannelRepository $channelRepository,
        protected ExchangeRateRepository $exchangeRateRepository,
        protected BrandRepository $brandRepository,
        protected SellerRepository $sellerRepository,
        protected CompanyRepository $companyRepository,
        protected LocaleRepository $localeRepository,
        protected CategoryRepository $categoryRepository,
    ) {
    }

    /**
     * Return all locales.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllLocales()
    {
        return $this->localeRepository->all()->sortBy('name');
    }

    public function getAdminEmailDetails(): array
    {
        return [
            'email' => config('mail.from.address', 'support@stegback.de'),
            'name' => config('mail.from.name', 'Stegback'),
        ];
    }

    public function getUserAddress($address)
    {

        if (is_array($address)) {
            // Handle array case (if applicable)
            $addressParts = [
                $address['Name'] ?? '',
                $address['street'] ?? '',
                $address['address'] ?? '',
                $address['city'] ?? '',
                $address['state'] ?? '',
                $address['postcode'] ?? '',
                $address['country'] ?? '',
                $address['phone'] ?? ''            ];
        } else {
            // Handle object case
            $addressParts = [
                $address->Name ?? '',
                $address->street ?? '',
                $address->address ?? '',
                $address->city ?? '',
                $address->state ?? '',
                $address->postcode ?? '',
                $address->country ?? '',
                $address->phone ?? ''
            ];
        }

        // Filter out empty or null values
        $filteredAddressParts = array_filter($addressParts, function ($part) {
            return !empty($part);
        });
        // Join the remaining parts with ', '
        return implode(', ', $filteredAddressParts) . '.';
    }

    public function getCustomerEmailDetails(): array
    {
        $user = session('tempUser');
        if (!$user) {
            $user = [
                'email' => auth()->user()->email ?? 'default@example.com',
                'name' => auth()->user()->name ?? 'Default User',
            ];
        }

        return [
            'email' => $user['email'],
            'name' => $user['name'],
        ];
    }

    /**
     * Returns current channel code.
     *
     * @return \Kartikey\Core\Interface\Channel
     */
    public function getCurrentChannelCode(): string
    {
        return $this->getCurrentChannel()?->code;
    }

        /**
     * Returns current channel models.
     *
     * @return \Kartikey\Core\Interface\Channel
     */
    public function getCurrentChannel(?string $hostname = null)
    {
        if (! $hostname) {
            $hostname = request()->getHttpHost();
        }

        if ($this->currentChannel) {
            return $this->currentChannel;
        }

        $this->currentChannel = $this->channelRepository->findWhereIn('hostname', [
            $hostname,
            'http://'.$hostname,
            'https://'.$hostname,
        ])->first();

        if (! $this->currentChannel) {
            $this->currentChannel = $this->channelRepository->first();
        }

        return $this->currentChannel;
    }

    /**
     * Get the base currency code.
     *
     * @return string
     */
    public function getBaseCurrencyCode(): string
    {
        return config('core.base_currency_code', 'EUR'); // Default to EUR if not set in configuration
    }

    public function getCurrentLocale(): string
    {
        $currentLocale = app()->getLocale();
        $defaultLocale = 'en';

        return ($currentLocale == $defaultLocale) ? $currentLocale : $defaultLocale;
    }

    /**
     * Get the current currency code.
     *
     * @return string
     */
    public function getCurrentCurrencyCode(): string
    {
        return $this->getBaseCurrencyCode();
    }
    /**
     * Returns current currency model.
     *
     * Will fallback to a static Euro currency if not set.
     *
     * @return \Kartikey\Core\Models\Currency
     */
    public function getCurrentCurrency()
    {
        if ($this->currentCurrency) {
            return $this->currentCurrency;
        }

        // Default to Euro currency if not set
        return $this->currentCurrency = $this->getDefaultCurrency();
    }


    /**
     * Format and convert price with currency symbol.
     *
     * @param  float  $price
     * @return string
     */
    public function currency($amount = 0)
    {
        if (is_null($amount)) {
            $amount = 0;
        }

        return $this->formatPrice($this->convertPrice($amount));
    }
    
    /**
     * Returns all currencies.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllCurrencies()
    {
        return $this->currencyRepository->all();
    }

    /**
     * Format price.
     */
    public function formatPrice(?float $price, ?string $currencyCode = null): string
    {
        if (is_null($price)) {
            $price = 0;
        }

        $currency = $currencyCode
            ? $this->getAllCurrencies()->where('code', $currencyCode)->first()
            : $this->getCurrentCurrency();

        // Cast to the Currency interface
        $currency = $this->castToCurrencyInterface($currency);

        return $this->formatCurrency($price, $currency);
    }

    /**
     * Cast the given object to the Currency interface.
     */
    protected function castToCurrencyInterface($currency): Currency
    {
        if (!$currency instanceof Currency) {
            // If the currency object lacks an 'id', use the 'code' property to find the CurrencyModel instance
            if (isset($currency->code)) {
                $currency = CurrencyModel::where('code', $currency->code)->first();
            } else {
                throw new \Exception('Invalid currency object');
            }
        }

        return $currency;
    }


    /**
     * Get the default currency.
     *
     * @return \Kartikey\Core\Models\Currency
     */
    protected function getDefaultCurrency()
    {
        return $this->currencyRepository->findOneWhere(['code' => static::DEFAULT_CURRENCY]);
    }


    /**
     * Converts price.
     *
     * @param  float  $amount
     * @param  string  $targetCurrencyCode
     * @return string
     */
    public function convertPrice($amount, $targetCurrencyCode = null)
    {
        // return (float) $amount;
        //Todo Impliment Curency REPO and EXCHANGE RATE

        $targetCurrency = !$targetCurrencyCode
            ? $this->getCurrentCurrency()
            : $this->currencyRepository->findOneByField('code', $targetCurrencyCode);
        if (!$targetCurrency) {
            return $amount;
        }

        $exchangeRate = $this->getExchangeRate($targetCurrency->id);

        if (!$exchangeRate) {
            return $amount;
        }

        return (float) $amount * $exchangeRate->rate;
    }

    /**
     * Returns exchange rates.
     *
     * @return object
     */

    //Todo Need to refine this more
    public function getExchangeRate($targetCurrencyId)
    {
        if (array_key_exists($targetCurrencyId, $this->exchangeRates)) {
            return $this->exchangeRates[$targetCurrencyId];
        }

        return $this->exchangeRates[$targetCurrencyId] = $this->exchangeRateRepository->findOneWhere([
            'target_currency' => $targetCurrencyId,
        ]);
    }

    public function getBrandId($name)
    {
        $brand = $this->brandRepository->findWhere(['name'=> $name])->first();

        return $brand['id'];
    }

    public function getBrandName($id)
    {

        $brand = $this->brandRepository->findWhere(['id'=> $id])->first();

        return $brand ? $brand->name : 'Unknown Brand';
    }

    public function getSellerName($id)
    {
        return $seller = $this->sellerRepository->getName( $id);
    }

    public function getSellerId($companyId)
    {
        $comapny = $this->companyRepository->with('details')->findWhere(['company_id'=> $companyId])->first();
        return $comapny['details']['id'];
    }

    public function getCategoryId($slug)
    {
        $category = $this->categoryRepository->findWhere(['slug'=> $slug])->first();
        return $category['id'];
    }

    public function getDiscountRatio($price,$salePrice)
    {
        if (isset($salePrice) && (($salePrice > 0 ) && $salePrice < $price))
        {

            $discountAmount = $price - $salePrice;
            if ($price != 0) {

                $discountPercentage = ($discountAmount / $price) * 100;
            } else {
                $discountPercentage = 0;
            }
            return round(number_format($discountPercentage, 2)).'%';

        }
        return false;
    }

    public function getDeliveryDate($days)
    {
        return Carbon::now()->addWeekdays($days)->format('D d M');
    }

    public function getMaxEstimateDeliveryDate($cartItemArray)
    {
        $maxDeliveryDays = 0;
        $maxDeliveryDate = null;

        foreach ($cartItemArray as $item) {
            if (isset($item['EstimateDeliveryDays']) && $item['EstimateDeliveryDays'] > $maxDeliveryDays) {
                $maxDeliveryDays = $item['EstimateDeliveryDays'];
                $maxDeliveryDate = $item['EstimateDeliveryDate'];
            }
        }

        return $maxDeliveryDate;
    }

        /**
     * Get channel code from request.
     *
     * @param  bool  $fallback  optional
     * @return string
     */
    public function getRequestedChannelCode($fallback = true)
    {
        $channelCode = request()->get('channel');

        if (! $fallback) {
            return $channelCode;
        }

        return $channelCode ?: ($this->getCurrentChannelCode() ?: $this->getDefaultChannelCode());
    }

        /**
     * Returns the default channel code configured in `config/app.php`.
     */
    public function getDefaultChannelCode(): string
    {
        return $this->getDefaultChannel()?->code;
    }

        /**
     * Returns default channel models.
     *
     * @return \Kartikey\Core\Interface\Channel
     */
    public function getDefaultChannel(): ?Channel
    {
        if ($this->defaultChannel) {
            return $this->defaultChannel;
        }

        $this->defaultChannel = $this->channelRepository->findOneByField('code', config('app.channel'));

        if ($this->defaultChannel) {
            return $this->defaultChannel;
        }

        return $this->defaultChannel = $this->channelRepository->first();
    }

    /**
     * Get locale code from request. Here if you want to use admin locale,
     * you can pass it as an argument.
     *
     * @param  string  $localeKey  optional
     * @param  bool  $fallback  optional
     * @return string
    */

    public function getRequestedLocaleCode($localeKey = 'locale', $fallback = true)
    {
        $localeCode = request()->get($localeKey);

        if (! $fallback) {
            return $localeCode;
        }

        return $localeCode ?: app()->getLocale();
    }


    /**
     * Retrieve information from payment configuration.
     */
    public function getConfigData(string $field, ?string $currentChannelCode = null, ?string $currentLocaleCode = null): mixed
    {
        return system_config()->getConfigData($field, $currentChannelCode, $currentLocaleCode);
    }

}
