<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TelegramDb extends Model
{
    protected $connection = 'telegram';
    protected $table = 'users';
}
