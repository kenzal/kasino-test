<?php

namespace App\Providers;

use Illuminate\Database\Grammar;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\ServiceProvider;
use Inertia\ResponseFactory;

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
        JsonResource::withoutWrapping();
    }
}
