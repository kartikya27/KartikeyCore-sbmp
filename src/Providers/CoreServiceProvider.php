<?php

namespace Kartikey\Core\Providers;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Kartikey\Core\Core;
use Kartikey\Core\Facades\Core as CoreFacade;
use Kartikey\Core\Facades\SystemConfig as SystemConfigFacades;
use Kartikey\Core\Interface\Channel;
use Kartikey\Core\Interface\CoreConfig;
use Kartikey\Core\Interface\Locale;
use Kartikey\Core\Models\Channel as ModelsChannel;
use Kartikey\Core\Models\CoreConfig as ModelsCoreConfig;
use Kartikey\Core\Models\Locale as ModelsLocale;
use Kartikey\Core\SystemConfig;

class CoreServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        include __DIR__ . '/../Http/helpers.php';

        $this->loadMigrationsFrom(__DIR__ . '/../../Database/Migrations');
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'core');
    }

    /**
     * Register services.
     */
    public function register(): void
    {
        $this->registerFacades();
        $this->app->bind(Channel::class, ModelsChannel::class);
        $this->app->bind(Locale::class, ModelsLocale::class);
        $this->app->bind(CoreConfig::class, ModelsCoreConfig::class);

        $this->registerConfig();
    }

    protected function registerConfig()
    {
        $this->mergeConfigFrom(
            dirname(__DIR__).'/Config/system.php',
            'core'
        );
    }
    /**
     * Register Bouncer as a singleton.
     */
    protected function registerFacades(): void
    {
        $loader = AliasLoader::getInstance();

        $loader->alias('core', CoreFacade::class);

        $this->app->singleton('core', function () {
            return app()->make(Core::class);
        });

        $loader->alias('system_config', SystemConfigFacades::class);

        $this->app->singleton('system_config', function () {
            return app()->make(SystemConfig::class);
        });
    }
}
