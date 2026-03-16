<?php

namespace App\Models;

use App\Services\TaskService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Scores extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saved(fn () => TaskService::clearScoresCache());
        static::deleted(fn () => TaskService::clearScoresCache());
    }

    public function tasks(){
        return $this->hasMany(Task::class, 'score_id', 'id');
    }
}
