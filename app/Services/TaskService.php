<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Sector;
use Illuminate\Support\Facades\Auth;

class TaskService {

    public function sectorList(){
        $user = Auth::user();

        if($user->isDirector() || $user->isMailer() || Auth::user()->isDeputy()){
            $sectors = Sector::with('users:id,name,sector_id,role_id')->get();
        }elseif ($user->isHead()) {
            $sectors = Sector::with('users:id,name,sector_id,role_id')->get();
        }else{
            $sectors = NULL;
        }

        return $sectors;
    }

    public function projectList(){
        $user = Auth::user();

        if($user->isDirector() || $user->isMailer() || Auth::user()->isDeputy() || $user->isHead()){
            $projects = Project::where('user_id', $user->id)->get();
        }else{
            $projects = NULL;
        }

        return $projects;
    }

}
