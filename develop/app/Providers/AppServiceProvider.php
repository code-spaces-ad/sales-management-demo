<?php

/**
 * @copyright © 2025 CodeSpaces
 */

namespace App\Providers;

use App\Enums\ReducedTaxFlagType;
use App\Enums\RoundingMethodType;
use App\Enums\SalesClassification;
use App\Enums\TaxCalcType;
use App\Enums\TaxType;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

/**
 * Class AppServiceProvider
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {}

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (\App::environment(['production'])) {
            \URL::forceScheme('https');
        }
        Paginator::useBootstrap();

        View::share('jsEnums', [
            'tax_type' => TaxType::toJavascriptArray(),
            'tax_calc_type' => TaxCalcType::toJavascriptArray(),
            'rounding_method_type' => RoundingMethodType::toJavascriptArray(),
            'reduced_tax_flag_type' => ReducedTaxFlagType::toJavascriptArray(),
            'sales_classification' => SalesClassification::ToJavascriptArray(),
        ]);
    }
}
