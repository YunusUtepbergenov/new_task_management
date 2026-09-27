<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MailDeadline extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_IN_REVIEW = 'in_review';

    public const STATUS_RETURNED = 'returned';

    public const STATUS_DONE = 'done';

    /**
     * @var list<string>
     */
    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_IN_REVIEW,
        self::STATUS_RETURNED,
        self::STATUS_DONE,
    ];

    protected $fillable = [
        'mail_item_id',
        'deadline',
        'status',
        'note',
        'completed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'deadline' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(MailItem::class, 'mail_item_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(MailFile::class);
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', '!=', self::STATUS_DONE)->whereDate('deadline', '<', today());
    }

    public function isOverdue(): bool
    {
        return $this->status !== self::STATUS_DONE && $this->deadline->lt(today());
    }

    public function overdueDays(): int
    {
        return $this->isOverdue() ? (int) $this->deadline->diffInDays(today()) : 0;
    }

    public function changeStatus(string $status, ?string $note = null): void
    {
        $this->update([
            'status' => $status,
            'note' => $note,
            'completed_at' => $status === self::STATUS_DONE ? ($this->completed_at ?? now()) : null,
        ]);
    }

    public function chipClass(): string
    {
        return 'mx-chip--'.match ($this->status) {
            self::STATUS_DONE => 'done',
            self::STATUS_IN_REVIEW => 'review',
            self::STATUS_RETURNED => 'returned',
            default => $this->isOverdue() ? 'late' : 'pending',
        };
    }
}
