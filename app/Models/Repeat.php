<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Repeat extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id',
        'repeat',
        'day',
        'deadline'
    ];

    public function task(){
        return $this->belongsTo(Task::class);
    }
}
