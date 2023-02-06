<?php

namespace App\Providers;

use Illuminate\Database\Grammar;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //Add uint256 Type to column types
        Grammar::macro('typeUint256', fn()=>'uint256');
        Grammar::macro('typeOverUnder', fn()=>'over_under');
    }
}
