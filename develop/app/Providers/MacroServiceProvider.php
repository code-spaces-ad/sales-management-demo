<?php

namespace App\Providers;

use App\Mixins\ExcelMacros;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\ServiceProvider;
use ReflectionException;

class MacroServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     *
     * @throws ReflectionException
     */
    public function register()
    {
        Collection::mixin(new ExcelMacros());
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
