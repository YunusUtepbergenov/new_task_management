<?php

namespace App\Providers;

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
        $birthdays = cache()->remember('birthdays', 60*60*24, function () {
            $date = now();

            return \App\Models\User::query()
                ->select('id', 'name', 'avatar', 'birth_date', 'leave')
                ->where(function ($q) use ($date) {
                    $q->whereMonth('birth_date', '>', $date->month)
                    ->orWhere(function ($q) use ($date) {
                        $q->whereMonth('birth_date', $date->month)
                            ->whereDay('birth_date', '>=', $date->day);
                    });
                })
                ->orderByRaw('MONTH(birth_date) asc')
                ->orderByRaw('DAY(birth_date) asc')
                ->get();
        });

        view()->share('birthdays', $birthdays);
    }
}
