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
        Paginator::useBootstrap();
        $birthdays = cache()->remember('birthdays', 60*60*24, function () {
            $date = now();

            return \App\Models\User::selectRaw("id, avatar, DATE_FORMAT(birth_date, '%m') as months,
            DATE_FORMAT(birth_date, '%d') as dates,
            name, birth_date")->
            whereMonth('birth_date', '>', $date->month)
            ->orWhere(function ($query) use ($date) {
                $query->whereMonth('birth_date', '=', $date->month)
                    ->whereDay('birth_date', '>=', $date->day);
            })->where('leave', 0)->orderBy("months",'ASC')->orderBy("dates", 'ASC')
            ->take(3)
            ->get();
        });

        view()->share('birthdays', $birthdays);
    }
}
