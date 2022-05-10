<?php

namespace App\Providers;

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
        $birthdays = cache()->remember('birthdays', 60*60*24, function () {
            $date = now();

            return \App\Models\User::selectRaw("DATE_FORMAT(birth_date, '%m') as months,
            DATE_FORMAT(birth_date, '%d') as dates,
            name, birth_date")->
            whereMonth('birth_date', '>', $date->month)
            ->orWhere(function ($query) use ($date) {
                $query->whereMonth('birth_date', '=', $date->month)
                    ->whereDay('birth_date', '>=', $date->day);
            })->orderBy("months",'ASC')->orderBy("dates", 'ASC')
            ->take(3)
            ->get();
        });

        view()->share('birthdays', $birthdays);
    }
}
