<?php

namespace GGPHP\Payment\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\AliasLoader;
use Webkul\Payment\Payment;
use Webkul\Payment\Facades\Payment as PaymentFacade;

class PaymentServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        include __DIR__ . '/../Http/routes.php';

        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'payment');
        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'payment');

        $this->publishes([
            __DIR__ . '/../Resources/views/payment' => resource_path('themes/velocity/views'),
        ]);
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerFacades();

        $this->registerConfig();
    }
    /**
     * Register Bouncer as a singleton.
     *
     * @return void
     */
    protected function registerFacades()
    {
        $loader = AliasLoader::getInstance();
        $loader->alias('payment', PaymentFacade::class);

        $this->app->singleton('payment', function () {
            return new Payment();
        });
    }
    /**
     * Register package config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/Config/paymentmethods.php', 'paymentmethods'
        );

        $this->mergeConfigFrom(
            dirname(__DIR__) . '/Config/system.php', 'core'
        );
    }
}
