<?php

namespace Kartikey\Core;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Kartikey\Core\Models\CoreConfig;
use Kartikey\Core\Repository\CoreConfigRepository;
use Kartikey\Core\SystemConfig\Item;

class SystemConfig
{
        /**
     * Items array.
     */
    public array $items = [];

        /**
     * Create a new class instance.
     *
     * @return void
     */
    public function __construct(protected CoreConfigRepository $coreConfigRepository) {}

    /**
     * Add Item.
     */
    public function addItem(Item $item): void
    {
        $this->items[] = $item;
    }

        /**
     * Get all configuration items.
     */
    public function getItems(): Collection
    {
        if (! $this->items) {
            $this->prepareConfigurationItems();
        }

        return collect($this->items)
            ->sortBy('sort');
    }


    /**
     * Prepare configuration items.
     */
    public function prepareConfigurationItems()
    {
        $configWithDotNotation = [];

        foreach ($this->retrieveCoreConfig() as $item) {
            $configWithDotNotation[$item['key']] = $item;
        }

        $configs = Arr::undot(Arr::dot($configWithDotNotation));

        foreach ($configs as $configItem) {
            $subConfigItems = $this->processSubConfigItems($configItem);

            $this->addItem(new Item(
                children: $subConfigItems,
                fields: $configItem['fields'] ?? null,
                icon: $configItem['icon'] ?? null,
                info: trans($configItem['info']) ?? null,
                key: $configItem['key'],
                name: trans($configItem['name']),
                route: $configItem['route'] ?? null,
                sort: $configItem['sort'],
            ));
        }
    }


    /**
     * Process sub config items.
     */
    private function processSubConfigItems($configItem): Collection
    {
        return collect($configItem)
            ->sortBy('sort')
            ->filter(fn ($value) => is_array($value) && isset($value['name']))
            ->map(function ($subConfigItem) {
                $configItemChildren = $this->processSubConfigItems($subConfigItem);

                return new Item(
                    children: $configItemChildren,
                    fields: $subConfigItem['fields'] ?? null,
                    icon: $subConfigItem['icon'] ?? null,
                    info: trans($subConfigItem['info']) ?? null,
                    key: $subConfigItem['key'],
                    name: trans($subConfigItem['name']),
                    route: $subConfigItem['route'] ?? null,
                    sort: $subConfigItem['sort'] ?? null,
                );
            });
    }


    /**
     * Get the config data.
     */
    public function getConfigData(string $field, ?string $currentChannelCode = null, ?string $currentLocaleCode = null): mixed
    {

        if (empty($currentChannelCode)) {
            $currentChannelCode = core()->getRequestedChannelCode();
        }
        if (empty($currentLocaleCode)) {
            $currentLocaleCode = core()->getRequestedLocaleCode();
        }

        $coreConfig = $this->getCoreConfig($field, $currentChannelCode, $currentLocaleCode);

        if (! $coreConfig) {
            return $this->getDefaultConfig($field);
        }

        return $coreConfig->value;
    }

     /**
     * Get core config values.
     */
    protected function getCoreConfig(string $field, ?string $channel, ?string $locale): ?CoreConfig
    {

        $fields = $this->getConfigField($field);

        if (! empty($fields['channel_based'])) {
            if (! empty($fields['locale_based'])) {
                $coreConfigValue = $this->coreConfigRepository->findOneWhere([
                    'code'         => $field,
                    'channel_code' => $channel,
                    'locale_code'  => $locale,
                ]);
            } else {
                $coreConfigValue = $this->coreConfigRepository->findOneWhere([
                    'code'         => $field,
                    'channel_code' => $channel,
                ]);
            }
        } else {
            if (! empty($fields['locale_based'])) {
                $coreConfigValue = $this->coreConfigRepository->findOneWhere([
                    'code'        => $field,
                    'locale_code' => $locale,
                ]);
            } else {
                $coreConfigValue = $this->coreConfigRepository->findOneWhere([
                    'code' => $field,
                ]);
            }
        }

        return $coreConfigValue;
    }


    /**
     * Get default config.
     */
    protected function getDefaultConfig(string $field): mixed
    {
        $configFieldInfo = $this->getConfigField($field);

        $fields = explode('.', $field);

        array_shift($fields);

        $field = implode('.', $fields);

        return Config::get($field, $configFieldInfo['default'] ?? null);
    }

    /**
     * Get config field.
     */
    public function getConfigField(string $fieldName): ?array
    {

        foreach ($this->retrieveCoreConfig() as $coreData) {

            if (! isset($coreData['fields'])) {
                continue;
            }

            foreach ($coreData['fields'] as $field) {
                $name = $coreData['key'].'.'.$field['name'];

                if ($name == $fieldName) {
                    return $field;
                }
            }
        }

        return null;
    }

    /**
     * Retrieve Core Config
     */
    private function retrieveCoreConfig(): array
    {
        static $items;
        if ($items) {
            return $items;
        }
        return $items = config('core');
    }

}
