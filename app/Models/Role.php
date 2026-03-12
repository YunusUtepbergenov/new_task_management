<?php

namespace App\Models;

use App\Services\TaskService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saved(fn () => TaskService::clearRolesCache());
        static::deleted(fn () => TaskService::clearRolesCache());
    }
}
