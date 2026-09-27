<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class MailDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'document_number',
        'document_date',
        'title',
        'description',
        'creator_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'document_date' => 'date',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(MailItem::class)->orderBy('position')->orderBy('id');
    }

    public function deadlines(): HasManyThrough
    {
        return $this->hasManyThrough(MailDeadline::class, MailItem::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(MailFile::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * Limit the query to documents the given user may see: everything for the director,
     * deputies and the mailer; otherwise documents with an item assigned to the user
     * (sector heads also see items assigned to their sector's employees).
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->canViewAllMails()) {
            return $query;
        }

        return $query->whereHas('items.executors', fn (Builder $executors) => $executors->where(function (Builder $visible) use ($user): void {
            $visible->where('users.id', $user->id);

            if ($user->isHead() && $user->sector_id) {
                $visible->orWhere('mail_item_user.sector_id', $user->sector_id);
            }
        }));
    }

    public function isVisibleTo(User $user): bool
    {
        return static::query()->whereKey($this->getKey())->visibleTo($user)->exists();
    }
}
