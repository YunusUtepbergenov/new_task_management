<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

$days = [
    1 => 'Monday',
    2 => 'Tuesday',
    3 => 'Wednesday',
    4 => 'Thursday',
    5 => 'Friday',
    6 => 'Saturday',
    7 => 'Sunday'
];

Schedule::command('tasks:generate-repeats')->dailyAt('01:00');
Schedule::command('telegram:deadline-reminders')->dailyAt('08:00');

Schedule::call(function() {
    DB::table('tasks')
        ->whereRaw('COALESCE(extended_deadline, deadline) <= ?', [Carbon::yesterday()])
        ->whereIn('status', ['Не прочитано' ,'Выполняется', 'Просроченный'])
        ->where('overdue', 0)
        ->update(['overdue' => 1]);
})->dailyAt('00:01');
