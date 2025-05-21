<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'creator_id',
        'user_id',
        'sector_id',
        'project_id',
        'type_id',
        'priority_id',
        'score_id',
        'name',
        'description',
        'deadline',
        'status',
        'overdue',
        'repeat_id',
        'total',
        'planning_type',
        'extended_deadline'
    ];

    public function username($id){
        $user = User::find($id);
        $name = Str::words($user->name, 2, '');
        return $name;
    }

    public function employee_name(){
        $name = Str::words($this->user->name, 2, '');
        return $name;
    }

    public function creator_name(){
        $name = Str::words($this->user->name, 2, '');
        return $name;
    }

    public function executers(){
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function type(){
        return $this->belongsTo(Type::class);
    }

    public function score(){
        return $this->belongsTo(Scores::class);
    }

    public function priority(){
        return $this->belongsTo(Priority::class);
    }

    public function response(){
        return $this->hasOne(Response::class);
    }

    public function project(){
        return $this->belongsTo(Project::class);
    }

    public function creator(){
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function files(){
        return $this->hasMany(File::class);
    }

    public function comments(){
        return $this->hasMany(Comment::class);
    }

    public function overdueTasks(){
        return $this->where('overdue', 1);
    }

    public function newTasks(){
        return $this->where('overdue', 0)->where('status', 'Не прочитано');
    }

    public function doingTasks(){
        return $this->where('overdue', 0)->where('status', 'Выполняется');
    }

    public function confirmTasks(){
        return $this->where('status', 'Ждет подтверждения');
    }

    public function finishedTasks(){
        return $this->where('overdue', 0)->where('status', 'Выполнено');
    }

    public function repeat(){
        return $this->hasOne(Repeat::class);
    }
}
