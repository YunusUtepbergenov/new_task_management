<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskLog extends Model
{
    protected $fillable = ['task_id', 'user_id', 'action', 'description'];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function translatedDescription(): string
    {
        $simple = [
            'created' => __('notifications.log_task_created'),
            'status_changed' => __('notifications.status_changed'),
            'submitted' => __('notifications.task_submitted_for_confirmation'),
            'rejected' => __('notifications.task_rejected'),
            'resubmitted' => __('notifications.submission_cancelled'),
            'users_changed' => __('notifications.log_responsible_changed'),
        ];

        if (isset($simple[$this->action])) {
            return $simple[$this->action];
        }

        if ($this->action === 'confirmed') {
            if (preg_match('/[\d.]+\s*$/', $this->description, $m)) {
                return __('notifications.task_confirmed_score') . ' ' . trim($m[0]);
            }
            return __('notifications.task_confirmed');
        }

        if ($this->action === 'edited' && preg_match('/:\s*(.+)$/', $this->description, $m)) {
            return __('notifications.log_task_edited') . ' ' . trim($m[1]);
        }

        if ($this->action === 'deadline_extended' && preg_match('/:\s*(.+)$/', $this->description, $m)) {
            return __('notifications.log_deadline_extended') . ' ' . trim($m[0]);
        }

        return $this->description;
    }
}
