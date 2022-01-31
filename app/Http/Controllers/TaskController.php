<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Task;
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
        $request->validate([
            'name' => 'required|min:3|max:255',
            'description' => 'required|min:3',
            'deadline' => 'required',
        ]);

        $date = str_replace('/', '-', $request->deadline);

        $task = Task::create([
            'creator_id' => Auth::user()->id,
            'user_id' => $request->user_id,
            'sector_id' => 1,
            'project_id' => $request->project_id,
            'name' => $request->name,
            'description' => $request->description,
            'deadline' => date("Y-m-d", strtotime($date)),
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

        return redirect()->back();
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
        //
    }
}
