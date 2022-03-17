<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'creator_id',
        'user_id',
        'sector_id',
        'project_id',
        'name',
        'description',
        'deadline',
        'status',
        'repeat_id',
    ];

    public function username($id){
        $user = User::find($id);
        return $user->name;
    }

    public function executers(){
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function response(){
        return $this->hasOne(Response::class);
    }

    public function project(){
        return $this->belongsTo(Project::class);
    }

    public function creator(){
        return $this->belongsTo(User::class);
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
        return $this->where('overdue', 0)->where('status', 'Новое');
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
