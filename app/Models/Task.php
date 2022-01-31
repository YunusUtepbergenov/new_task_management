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
        'status'
    ];

    public function username($id){
        $user = User::find($id);
        return $user->name;
    }

    public function executers(){
        return $this->belongsToMany(User::class)->withTimestamps();
    }
}
