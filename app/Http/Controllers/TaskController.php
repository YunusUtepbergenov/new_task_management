<?php

namespace App\Http\Controllers;

use App\Events\TaskCreatedEvent;
use App\Models\File;
use App\Models\Project;
use App\Models\Repeat;
use App\Models\Response;
use App\Models\Sector;
use App\Models\Task;
use App\Models\TaskUser;
use Carbon\Carbon;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use phpDocumentor\Reflection\Types\Null_;

class TaskController extends Controller
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

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|min:3|max:255',
            'description' => 'required|min:3',
            'deadline' => 'required|date_format:Y-m-d|after:today',
            'file.*' => 'nullable|file|max:5000'
        ]);

        if($request->repeat_check != "on"){
            $new_deadline = $request->deadline;

            $task = Task::create([
                'creator_id' => $request->creator_id,
                'user_id' => $request->user_id,
                'project_id' => $request->project_id,
                'name' => $request->name,
                'description' => $request->description,
                'deadline' => $new_deadline,
                'status' => 'Новое',
            ]);

            $task->executers()->sync($request->helpers, false);
            if($request->hasFile('file')){
                foreach($request->file as $file){
                    $filename = time().$file->getClientOriginalName();
                    Storage::disk('local')->putFileAs(
                        'files/',
                        $file,
                        $filename
                    );
                    $fileModel = new File;
                    $fileModel->task_id = $task->id;
                    $fileModel->name = $filename;
                    $fileModel->save();
                }
            }
            event(new TaskCreatedEvent($task));
        }
        else{
            if($request->repeat == 'weekly'){
                $today = intval(date('N', strtotime(date('l'))));

                foreach($request->days as $day){
                    $day_of_week = intval($day);

                    if($today <= $day_of_week){
                        $new_deadline = date('Y-m-d', strtotime(date('l', strtotime($this->days[$day_of_week].' this week'))));
                    }else{
                        $new_deadline = date('Y-m-d', strtotime(date('l', strtotime($this->days[$day_of_week].' next week'))));
                    }

                    $task = Task::create([
                        'creator_id' => $request->creator_id,
                        'user_id' => $request->user_id,
                        'project_id' => $request->project_id,
                        'name' => $request->name,
                        'description' => $request->description,
                        'deadline' => $new_deadline,
                        'status' => 'Новое',
                    ]);

                    $task->executers()->sync($request->helpers, false);
                    if($request->hasFile('file')){
                        foreach($request->file as $file){
                            $filename = time().$file->getClientOriginalName();
                            Storage::disk('local')->putFileAs(
                                'files/',
                                $file,
                                $filename
                            );
                            $fileModel = new File;
                            $fileModel->task_id = $task->id;
                            $fileModel->name = $filename;
                            $fileModel->save();
                        }
                    }

                    Repeat::create([
                        'task_id' => $task->id,
                        'repeat' => 'weekly',
                        'day' => $day,
                        'deadline' => $request->deadline,
                    ]);
                    event(new TaskCreatedEvent($task));
                }
            }
            elseif($request->repeat == 'monthly'){
                $today = intval(date('d'));
                $day_of_month = $request->month_day;

                if($today <= intval($day_of_month)){
                    $new_deadline = date('Y-m-d', mktime(0,0,0, date("m"),$day_of_month, date('Y')));
                }
                else{
                    $new_deadline = date('Y-m-d', mktime(0,0,0, date("m") + 1,$day_of_month, date('Y')));
                }

                $task = Task::create([
                    'creator_id' => $request->creator_id,
                    'user_id' => $request->user_id,
                    'project_id' => $request->project_id,
                    'name' => $request->name,
                    'description' => $request->description,
                    'deadline' => $new_deadline,
                    'status' => 'Новое',
                ]);

                $task->executers()->sync($request->helpers, false);
                if($request->hasFile('file')){
                    foreach($request->file as $file){
                        $filename = time().$file->getClientOriginalName();
                        Storage::disk('local')->putFileAs(
                            'files/',
                            $file,
                            $filename
                        );
                        $fileModel = new File;
                        $fileModel->task_id = $task->id;
                        $fileModel->name = $filename;
                        $fileModel->save();
                    }
                }

                Repeat::create([
                    'task_id' => $task->id,
                    'repeat' => 'monthly',
                    'day' => $day_of_month,
                    'deadline' => $request->deadline,
                ]);
                event(new TaskCreatedEvent($task));
            }
        }
    }

    public function update(Request $request)
    {
        // $repeat_arr = ['ordinary', 'weekly', 'monthly', 'quarterly'];
        $request->validate([
            'name' => 'required|min:3|max:255',
            'description' => 'required|min:3',
            'deadline' => 'required|date_format:Y-m-d|after:today',
            'file.*' => 'nullable|file|max:5000'
        ]);

        $task = Task::where('id', $request->id)->first();

        // if($request->repeat_check != "on"){
        //     $request->repeat = 'ordinary';
        // }

        $task->update([
            'creator_id' => $request->creator_id,
            'user_id' => $request->user_id,
            'project_id' => $request->project_id,
            'name' => $request->name,
            'description' => $request->description,
            'deadline' => $request->deadline,
            'status' => 'Новое',
            'repeat' => $request->repeat
        ]);

        $task->executers()->detach();
        $task->executers()->sync($request->helpers, false);

        if($request->hasFile('file')){
            $files = File::where('task_id', $task->id)->get();
            foreach($files as $file){
                $file->delete();
                Storage::delete('files/'.$file->name);
            }
            foreach($request->file as $file){
                $filename = time().$file->getClientOriginalName();
                Storage::disk('local')->putFileAs(
                    'files/',
                    $file,
                    $filename
                );
                $fileModel = new File;
                $fileModel->task_id = $task->id;
                $fileModel->name = $filename;
                $fileModel->save();
            }
        }
    }

    public function destroy($id)
    {
        $task = Task::where('id', $id)->first();
        $executers = TaskUser::where('task_id', $task->id)->get();

        if($executers){
            foreach ($executers as $executer) {
                $executer->delete();
            }
        }
        if($task->response){
            if($task->response->filename){
                Storage::delete('files/responses/'.$task->response->filename);
            }
            $task->response->delete();
        }

        if($task->files){
            foreach($task->files as $file){
                Storage::delete('files/'.$file->name);
                $file->delete();
            }
        }

        $task->delete();

        return back();
    }

    public function destroyRepeat($id){
        $repeat = Repeat::where('id', $id)->first();
        if($repeat)
            $repeat->delete();

        $tasks = Task::where('repeat_id', $repeat->id)->get();

        foreach($tasks as $task){
            $task->update([
                'repeat_id' => Null
            ]);
        }

        return back();
    }

    public function changeStatus(Request $request, $id){
        $task = Task::where('id', $id)->first();

        if($request->status == "Started"){
            $task->update(['status' => "Выполняется"]);
        }
        else if($request->status == "Submitted"){
            $task->update(['status' => "Ждет подтверждения"]);
        }
        return redirect()->back();
    }
}
