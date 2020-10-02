<?php

namespace GGPHP\Payment\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;

/**
 * Event ServiceProvider
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Event::listen(['bagisto.shop.layout.head', 'bagisto.admin.layout.head'], function($viewRenderEventManager) {
            $viewRenderEventManager->addTemplate('ggphp-payment::payment.style');
        });
    }
}
