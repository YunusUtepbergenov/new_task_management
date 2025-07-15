<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TurnstileLog extends Model
{
    use HasFactory;

    protected $connection = 'turnstile';
    protected $table = 'user_logs';
    public $timestamps = false;

    protected $fillable = ['id', 'auth_datetime', 'auth_date', 'auth_time', 'device_name'];
}
