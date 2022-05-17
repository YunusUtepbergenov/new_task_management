<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
use PDO;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'sector_id',
        'role_id',
        'phone',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
    ];

    protected $dates = ['birth_date'];

    public function role(){
        return $this->belongsTo(Role::class);
    }

    public function tasks(){
        return $this->hasMany(Task::class);
    }

    public function helpers(){
        return $this->belongsToMany(Task::class);
    }

    public function overdueTasks(){
        return $this->tasks()->where('overdue', 1);
    }

    public function newTasks(){
        return $this->tasks()->where('overdue', 0)->where('status', 'Новое');
    }

    public function doingTasks(){
        return $this->tasks()->where('overdue', 0)->where('status', 'Выполняется');
    }
    public function confirmTasks(){
        return $this->tasks()->where('overdue', 0)->where('status', 'Ждет подтверждения');
    }
    public function finishedTasks(){
        return $this->tasks()->where('overdue', 0)->where('status', 'Выполнено');
    }

    public function closedTasks(){
        return $this->tasks()->where('status', 'Выполнено');
    }

    public function sector(){
        return $this->belongsTo(Sector::class);
    }

    public function isDirector(){
        return $this->role->name == "Директор";
    }

    public function isMailer(){
        return $this->role->name === "Заведующий канцелярии";
    }

    public function isHead(){
        return $this->role->name === "Заведующий сектором";
    }

    public function isHR(){
        return $this->role->name === "Спецалист по работе с персоналом";
    }

    public function isDeputy(){
        return $this->role->name === "Заместитель директора";
    }

    public function isAccountant(){
        return $this->role->name === "Главный бухгалтер" || $this->role->name === "Бухгалтер";
    }
}
