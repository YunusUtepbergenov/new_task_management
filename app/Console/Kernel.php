<?php

namespace App\Console;

use App\Models\Task;
use Carbon\Carbon;
use DateTime;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;

class Kernel extends ConsoleKernel
{
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
            $tasks = Task::where('repeat', 'daily')->get();
            foreach($tasks as $task){
                if($task->deadline <= Carbon::now()){
                    Task::create([
                        'creator_id' => $task->creator_id,
                        'user_id' => $task->user_id,
                        'project_id' => $task->project_id,
                        'name' => $task->name,
                        'description' => $task->description,
                        'deadline' => date('Y-m-d'),
                        'status' => 'Новое',
                        'repeat' => 'ordinary',
                        'repeat_id' => $task->id
                    ]);
                }
            }
        })->dailyAt('01:00');

        $schedule->call(function(){
            $tasks = Task::where('repeat', 'weekly')->get();
            foreach($tasks as $task){
                $date = new DateTime();
                $deadline = strtotime($task->deadline);
                if($task->deadline <= Carbon::now()){
                    $day_of_week = date('l', $deadline);
                    $new_deadline = $date->modify('next '.$day_of_week)->format('Y-m-d');

                    Task::create([
                        'creator_id' => $task->creator_id,
                        'user_id' => $task->user_id,
                        'project_id' => $task->project_id,
                        'name' => $task->name,
                        'description' => $task->description,
                        'deadline' => $new_deadline,
                        'status' => 'Новое',
                        'repeat' => 'ordinary',
                        'repeat_id' => $task->id
                    ]);
                }
            }
        })->weeklyOn(1, '01:00');

        $schedule->call(function(){
            $tasks = Task::where('repeat', 'monthly')->get();
            foreach($tasks as $task){
                $date = new DateTime();
                $deadline = strtotime($task->deadline);
                if($task->deadline <= Carbon::now()){
                    $day_of_month = date('d', $deadline);
                    $new_deadline = date('Y-m-'.$day_of_month);

                    Task::create([
                        'creator_id' => $task->creator_id,
                        'user_id' => $task->user_id,
                        'project_id' => $task->project_id,
                        'name' => $task->name,
                        'description' => $task->description,
                        'deadline' => $new_deadline,
                        'status' => 'Новое',
                        'repeat' => 'ordinary',
                        'repeat_id' => $task->id
                    ]);
                }
            }
        })->monthly();

        $schedule->call(function(){
            $tasks = Task::where('repeat', 'quarterly')->get();
            foreach($tasks as $task){
                $date = new DateTime();
                $deadline = strtotime($task->deadline);
                if($task->deadline <= Carbon::now()){
                    $day_of_month = date('d', $deadline);
                    $new_deadline = date('Y-m-'.$day_of_month);

                    Task::create([
                        'creator_id' => $task->creator_id,
                        'user_id' => $task->user_id,
                        'project_id' => $task->project_id,
                        'name' => $task->name,
                        'description' => $task->description,
                        'deadline' => $new_deadline,
                        'status' => 'Новое',
                        'repeat' => 'ordinary',
                        'repeat_id' => $task->id
                    ]);
                }
            }
        })->quarterly();

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
