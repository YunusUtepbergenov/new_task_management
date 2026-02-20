<?php

namespace App\View\Composers;

use App\Models\User;
use Illuminate\View\View;

class BirthdayComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        $birthdays = cache()->remember('birthdays', 60 * 60 * 24, function () {
            $date = now();

            return User::query()
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

        $view->with('birthdays', $birthdays);
    }
}
