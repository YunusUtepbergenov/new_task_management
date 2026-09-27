<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class MailItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'mail_document_id',
        'clause',
        'content',
        'position',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(MailDocument::class, 'mail_document_id');
    }

    /**
     * Assigned employees. The pivot keeps the employee's sector at assignment time,
     * so history and sector reports stay correct after transfers or dismissals.
     */
    public function executors(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['is_main', 'sector_id'])
            ->withTimestamps()
            ->orderByDesc('mail_item_user.is_main');
    }

    public function deadlines(): HasMany
    {
        return $this->hasMany(MailDeadline::class)->orderBy('deadline');
    }

    /**
     * Items with at least one deadline that is not done yet.
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereHas('deadlines', fn (Builder $deadlines) => $deadlines->where('status', '!=', MailDeadline::STATUS_DONE));
    }

    /**
     * Same visibility as the inbox: everything for the director, deputies and the mailer;
     * otherwise the user's own items, plus their sector's items for sector heads.
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->canViewAllMails()) {
            return $query;
        }

        return $query->whereHas('executors', fn (Builder $executors) => $executors->where(function (Builder $visible) use ($user): void {
            $visible->where('users.id', $user->id);

            if ($user->isHead() && $user->sector_id) {
                $visible->orWhere('mail_item_user.sector_id', $user->sector_id);
            }
        }));
    }

    public function mainExecutor(): ?User
    {
        return $this->executors->first(fn (User $user): bool => (bool) $user->pivot->is_main);
    }

    /**
     * @return Collection<int, User>
     */
    public function coExecutors(): Collection
    {
        return $this->executors->reject(fn (User $user): bool => (bool) $user->pivot->is_main)->values();
    }
}
