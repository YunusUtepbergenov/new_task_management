<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Digest extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'user_id',
        'sector_id',
        'paper',
        'link',
        'file'
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
