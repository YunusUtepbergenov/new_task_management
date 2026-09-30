<?php

namespace App\Models;

use Carbon\Carbon;
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
        'sent_at',
        'completed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'deadline' => 'date',
            'sent_at' => 'datetime',
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

    /**
     * Still open and past its date. A result sent to edo.ijro.uz by the deadline is on time
     * while it waits for approval, even after the date has passed.
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', '!=', self::STATUS_DONE)
            ->whereDate('deadline', '<', today())
            ->whereNot(fn (Builder $sentOnTime) => $sentOnTime
                ->where('status', self::STATUS_IN_REVIEW)
                ->whereRaw('DATE(sent_at) <= deadline'));
    }

    public function isOverdue(): bool
    {
        if ($this->status === self::STATUS_DONE || $this->deadline->gte(today())) {
            return false;
        }

        return ! ($this->status === self::STATUS_IN_REVIEW && $this->lateDays() === 0);
    }

    public function overdueDays(): int
    {
        return $this->isOverdue() ? (int) $this->deadline->diffInDays(today()) : 0;
    }

    /**
     * Counted as past due in the report: closed or sent later than the deadline. When the
     * app does not know when it was sent or closed (imported from Excel), the deadline date
     * decides, as in the Excel sheet.
     */
    public function isPastDue(): bool
    {
        if ($this->status === self::STATUS_DONE) {
            return ($this->lateDays() ?? ($this->deadline->lt(today()) ? 1 : 0)) > 0;
        }

        return $this->isOverdue();
    }

    /**
     * When the executor's part was done: the first sending to edo.ijro.uz, or the closing
     * date when the deadline was closed without being sent.
     */
    public function submittedAt(): ?Carbon
    {
        return $this->sent_at ?? $this->completed_at;
    }

    /**
     * Days the result was sent or closed after the deadline: 0 when on time, null while the
     * deadline is not sent or closed, or when the dates are unknown (imported from Excel).
     */
    public function lateDays(): ?int
    {
        $submittedAt = $this->submittedAt();

        if (! in_array($this->status, [self::STATUS_IN_REVIEW, self::STATUS_DONE], true) || ! $submittedAt) {
            return null;
        }

        return max(0, (int) $this->deadline->diffInDays($submittedAt->copy()->startOfDay(), false));
    }

    public function changeStatus(string $status, ?string $note = null): void
    {
        $this->update([
            'status' => $status,
            'note' => $note,
            // The latest sending counts: a return cancels the earlier one, so a result re-sent
            // after the deadline is late. Saving a note on a sent deadline keeps its date.
            'sent_at' => match ($status) {
                self::STATUS_PENDING, self::STATUS_RETURNED => null,
                self::STATUS_IN_REVIEW => $this->status === self::STATUS_IN_REVIEW ? $this->sent_at : now(),
                default => $this->sent_at,
            },
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
