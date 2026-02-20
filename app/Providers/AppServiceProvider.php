<?php

namespace App\Providers;

use App\View\Composers\BirthdayComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;

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

        if($this->app->environment('production') || $this->app->environment('staging'))
        {
            \URL::forceScheme('https');
        }

        Paginator::useBootstrap();

        View::composer('layouts.main', BirthdayComposer::class);
    }
}
