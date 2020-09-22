<?php

namespace GGPHP\Payment\Providers;

use Konekt\Concord\BaseModuleServiceProvider;

class ModuleServiceProvider extends BaseModuleServiceProvider
{
    protected $models = [
        \GGPHP\Payment\Models\UserStripe::class,
        \GGPHP\Payment\Models\OrderStripe::class,
    ];
}
