<?php

namespace App\Models;

use App\Services\TaskService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Sector extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saved(fn () => TaskService::clearSectorsCache());
        static::deleted(fn () => TaskService::clearSectorsCache());
    }

    public function users(){
        return $this->hasMany(User::class)->where('leave', 0);
    }

    public function tasks(){
        return $this->hasMany(Task::class);
    }

    public function weeklyTasks()
    {
        $startOfWeek = Carbon::now()->startOfWeek(); // Monday
        $endOfWeek = Carbon::now()->endOfWeek();     // Sunday
    
        return $this->tasks()
            ->whereBetween('deadline', [$startOfWeek, $endOfWeek])
            ->orderBy('deadline')
            ->get();
    }

    public function head(){
        return $this->users()->where('role_id', 2)->first();
    }
}
