<?php

namespace App\Console;

use App\Events\TaskCreatedEvent;
use App\Models\Repeat;
use App\Models\Task;
use Carbon\Carbon;
use DateTime;
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
        $schedule->call(function(){
            DB::table('tasks')->where('deadline', '<=', Carbon::yesterday())->where('status', '<>', 'Просроченный')->whereIn('status', ['Новое' ,'Выполняется'])->update(['status' => 'Просроченный']);
        })->dailyAt('00:01');

        $schedule->call(function(){
            $repeats = Repeat::where('repeat', 'weekly')->where('deadline', '>', Carbon::now())->get();
            foreach($repeats as $repeat){
                $day_of_week = $repeat->day;
                $new_deadline = date('Y-m-d', strtotime(date('l', strtotime($this->days[$day_of_week].' this week'))));

                if(!Task::where('name', $repeat->task->name)->where('user_id', $repeat->task->user_id)->where('deadline', $new_deadline)->first()){
                    $task = Task::create([
                        'creator_id' => $repeat->task->creator_id,
                        'user_id' => $repeat->task->user_id,
                        'project_id' => $repeat->task->project_id,
                        'name' => $repeat->task->name,
                        'description' => $repeat->task->description,
                        'deadline' => $new_deadline,
                        'status' => 'Новое',
                    ]);
                    event(new TaskCreatedEvent($task));
                }
            }
        })->weeklyOn(1, '01:00');

        $schedule->call(function(){
            $repeats = Repeat::where('repeat', 'monthly')->where('deadline', '>', Carbon::now())->get();
            foreach($repeats as $repeat){
                $day_of_month = $repeat->day;
                $new_deadline = date('Y-m-'.$day_of_month);
                $sample = Task::where('name', $repeat->task->name)->where('user_id', $repeat->task->user_id)->where('deadline', $new_deadline)->get()->isEmpty();
                error_log($sample);

                if($sample){
                    Task::create([
                        'creator_id' => $repeat->task->creator_id,
                        'user_id' => $repeat->task->user_id,
                        'project_id' => $repeat->task->project_id,
                        'name' => $repeat->task->name,
                        'description' => $repeat->task->description,
                        'deadline' => $new_deadline,
                        'status' => 'Новое',
                    ]);
                }
            }
        })->monthlyOn(1, '03:00');

        // $schedule->call(function(){
        //     $tasks = Task::where('repeat', 'quarterly')->get();
        //     foreach($tasks as $task){
        //         $date = new DateTime();
        //         $deadline = strtotime($task->deadline);
        //         if($task->deadline <= Carbon::now()){
        //             $day_of_month = date('d', $deadline);
        //             $new_deadline = date('Y-m-'.$day_of_month);

        //             Task::create([
        //                 'creator_id' => $task->creator_id,
        //                 'user_id' => $task->user_id,
        //                 'project_id' => $task->project_id,
        //                 'name' => $task->name,
        //                 'description' => $task->description,
        //                 'deadline' => $new_deadline,
        //                 'status' => 'Новое',
        //                 'repeat' => 'ordinary',
        //                 'repeat_id' => $task->id
        //             ]);
        //         }
        //     }
        // })->quarterly();

        // $schedule->call(function(){
        //     DB::table('tasks')->where('deadline', '<', Carbon::now())->where('status', '<>', 'Просроченный')->whereIn('status', ['Новое' ,'Выполняется'])->update(['status' => 'Просроченный']);
        // })->everyMinute();
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
