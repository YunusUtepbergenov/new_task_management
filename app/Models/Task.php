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
        'repeat',
        'repeat_deadline',
        'repeat_id'
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
        return $this->where('status', 'Просроченный');
    }

    public function newTasks(){
        return $this->where('status', 'Новое');
    }

    public function doingTasks(){
        return $this->where('status', 'Выполняется');
    }
    public function confirmTasks(){
        return $this->where('status', 'Ждет подтверждения');
    }
    public function finishedTasks(){
        return $this->where('status', 'Выполнено');
    }
}
