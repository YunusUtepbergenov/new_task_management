<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Scores;
use App\Models\Sector;
use App\Models\Type;
use Illuminate\Support\Facades\Auth;
use phpDocumentor\Reflection\Types\Null_;

class TaskService {

    public function sectorList(){
        $user = Auth::user();

        if($user->isDirector() || $user->isMailer() || $user->isDeputy()){
            $sectors = Sector::with(['users' => function($query){
                $query->select(['id','name','sector_id','role_id'])->where('leave', 0);
            }])->get();
        }elseif ($user->isHead()) {
            $sectors = Sector::with('users:id,name,sector_id,role_id')->get();
        }else{
            $sectors = NULL;
        }

        return $sectors;
    }

    public function projectList(){
        $user = Auth::user();

        if($user->isDirector() || $user->isMailer() || $user->isDeputy() || $user->isHead() || $user->isHR()){
            $projects = Project::where('user_id', $user->id)->orderBy('created_at', 'DESC')->get();
        }else{
            $projects = NULL;
        }

        return $projects;
    }

    public function typeList(){
        $user = Auth::user();

        if($user->isDirector() || $user->isMailer() || $user->isDeputy() || $user->isHead()){
            $types = Type::all();
        }else{
            $types = NULL;
        }

        return $types;
    }

    public function scoresList(){
        $types = Scores::whereIn('id', range(1, 48))->get();
        return $types;
    }

    public function hrList(){
        $types = Scores::whereIn('id', range(49, 57))->get();
        return $types;
    }
    public function accountantList(){
        $types = Scores::whereIn('id', range(58, 64))->get();
        return $types;
    }
    public function lawyerList(){
        $types = Scores::whereIn('id', range(65, 71))->get();
        return $types;
    }
    public function maintainerList(){
        $types = Scores::whereIn('id', range(72, 75))->get();
        return $types;
    }
    public function ictList(){
        $types = Scores::whereIn('id', range(76, 80))->get();
        return $types;
    }
}
