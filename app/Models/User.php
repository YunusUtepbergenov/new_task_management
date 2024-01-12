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
use phpDocumentor\Reflection\Types\Null_;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    // use TwoFactorAuthenticatable;

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
        'birth_date',
        'avatar',
        'leave',
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

    public function digests(){
        return $this->hasMany(Digest::class);
    }

    public function mediaTasks($start, $end){
        return $this->tasks()->whereBetween('tasks.deadline', [$start, $end])->whereIn('tasks.type_id', [4,12]);
    }

    public function helpers(){
        return $this->belongsToMany(Task::class);
    }

    public function filterTasks($start, $end){
        return $this->tasks()->whereBetween('deadline', [$start, $end]);
    }

    public function overdueFilter($start, $end){
        return $this->tasks()->whereBetween('deadline', [$start, $end])->where('overdue', 1);
    }

    public function newFilter($start, $end){
        return $this->tasks()->whereBetween('deadline', [$start, $end])->where('overdue', 0)->where('status', 'Новое');
    }

    public function confirmFilter($start, $end){
        return $this->tasks()->whereBetween('deadline', [$start, $end])->where('overdue', 0)->where('status', 'Ждет подтверждения');
    }

    public function priority_filterTasks($start, $end){
        return $this->tasks()->whereBetween('deadline', [$start, $end])->where('priority_id', '<>', 4);
    }

    public function priority_doneFilter($start, $end){
        return $this->tasks()->whereBetween('deadline', [$start, $end])->where('status', 'Выполнено')->where('priority_id', '<>', 4);
    }

    public function simple_priority_filterTasks($start, $end){
        return $this->tasks()->whereBetween('deadline', [$start, $end])->where('priority_id', 1);
    }

    public function simple_priority_doneFilter($start, $end){
        return $this->tasks()->whereBetween('deadline', [$start, $end])->whereIn('status', ['Выполнено', 'Ждет подтверждения'])->where('priority_id', 1);
    }

    public function mid_priority_filterTasks($start, $end){
        return $this->tasks()->whereBetween('deadline', [$start, $end])->where('priority_id', 2);
    }

    public function mid_priority_doneFilter($start, $end){
        return $this->tasks()->whereBetween('deadline', [$start, $end])->whereIn('status', ['Выполнено', 'Ждет подтверждения'])->where('priority_id', 2);
    }

    public function high_priority_filterTasks($start, $end){
        return $this->tasks()->whereBetween('deadline', [$start, $end])->where('priority_id', 3);
    }

    public function high_priority_doneFilter($start, $end){
        return $this->tasks()->whereBetween('deadline', [$start, $end])->whereIn('status', ['Выполнено', 'Ждет подтверждения'])->where('priority_id', 3);
    }


    public function very_high_priority_filterTasks($start, $end){
        return $this->tasks()->whereBetween('deadline', [$start, $end])->where('priority_id', 4);
    }

    public function very_high_priority_doneFilter($start, $end){
        return $this->tasks()->whereBetween('deadline', [$start, $end])->whereIn('status', ['Выполнено', 'Ждет подтверждения'])->where('priority_id', 4);
    }

    public function kpiFilter($start, $end, $category_id){
        return $this->tasks()->whereBetween('deadline', [$start, $end])->where('score_id', $category_id)->where('status', 'Выполнено')->sum('total');
    }

    public function kpiCalculate(){
        $score = 0;
        $startDate = date('Y-m-01');
        $endDate = date('Y-m-t');

        $categories = Scores::with(['tasks' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('deadline', [$startDate, $endDate])->where('user_id', $this->id)->where('status', 'Выполнено');
        }])->get();
    
        foreach($categories as $category){
            $cat_score = $category->tasks->sum('total');
            if(isset($category->limit) && $cat_score > $category->limit)
                $score += $category->limit;
            else
                $score += $cat_score;
        }
        return $score;
    }

    public function ovrKpiCalculate(){
        $score = 0;
        $startDate = date('Y-m-01');
        $endDate = date('Y-m-t');

        $categories = Scores::with(['tasks' => function ($query) use ($startDate, $endDate) {
            $query->whereBetween('deadline', [$startDate, $endDate])->where('user_id', $this->id)->where('status', 'Выполнено');
        }])->get();
    
        foreach($categories as $category){
            $score += $category->tasks->sum('total');
        }
        return $score;
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
