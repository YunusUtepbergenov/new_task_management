<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;

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

            return \App\Models\User::select("id", "name", "avatar", "birth_date", "leave", DB::raw('DATE_FORMAT(birth_date, "%m") as months'), DB::raw('DATE_FORMAT(birth_date, "%d") as dates'))->
            whereMonth('birth_date', '>', $date->month)
            ->orWhere(function ($query) use ($date) {
                $query->whereMonth('birth_date', '=', $date->month)
                    ->whereDay('birth_date', '>=', $date->day);
            })->orderBy("months",'ASC')->orderBy("dates", 'ASC')
            ->get();
        });

        view()->share('birthdays', $birthdays);
    }
}
