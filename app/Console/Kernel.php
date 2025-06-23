<?php

namespace App\Console;

use App\Events\TaskCreatedEvent;
use App\Models\Repeat;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;

class Kernel extends ConsoleKernel
{
    public $days = array(
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
        7 => 'Sunday'
    );
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('tasks:generate-repeats')->dailyAt('01:00');

        $schedule->call(function(){
            DB::table('tasks')
                    ->whereRaw('COALESCE(extended_deadline, deadline) <= ?', [Carbon::yesterday()])
                    ->whereIn('status', ['Не прочитано' ,'Выполняется', 'Просроченный'])
                    ->where('overdue', 0)->update(['overdue' => 1]);
        })->dailyAt('00:01');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
