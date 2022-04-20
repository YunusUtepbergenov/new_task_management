<?php

namespace App\Services;

use App\Models\Project;

class ProjectService {

    public function projectsList($tasks){
        $projects_arr = array();

        $user_projects = collect([]);

        foreach($tasks as $task){
            array_push($projects_arr, $task->project->id);
        }

        foreach($projects_arr as $project){
            $project_collection = Project::where('id', $project)->first();
            $user_projects = $user_projects->merge([$project_collection]);
        }

        return $user_projects;
    }
}
