<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Scores extends Model
{
    use HasFactory;

    public function tasks(){
        return $this->hasMany(Task::class, 'score_id', 'id');
    }
}
