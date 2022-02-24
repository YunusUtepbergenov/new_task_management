<?php

namespace App\Http\Controllers;

use App\Events\TaskCreatedEvent;
use App\Models\File;
use App\Models\Project;
use App\Models\Sector;
use App\Models\Task;
use App\Models\TaskUser;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $repeat_arr = ['ordinary','daily', 'weekly', 'monthly', 'quarterly'];
        $request->validate([
            'name' => 'required|min:3|max:255',
            'description' => 'required|min:3',
            'deadline' => 'required|date_format:Y-m-d|after:today',
        ]);

        if($request->repeat_check != "on"){
            $request->repeat = 'ordinary';
        }

        if(in_array($request->repeat, $repeat_arr)){
            $task = Task::create([
                'creator_id' => $request->creator_id,
                'user_id' => $request->user_id,
                'project_id' => $request->project_id,
                'name' => $request->name,
                'description' => $request->description,
                'deadline' => $request->deadline,
                'status' => 'Новое',
                'repeat' => $request->repeat
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
        }else{
            abort(404);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $task = Task::where('id', $id)->first();
        $executers = TaskUser::where('task_id', $task->id)->get();

        if($executers){
            foreach ($executers as $executer) {
                $executer->delete();
            }
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
