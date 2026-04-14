<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FeatureAnnouncement extends Model
{
    protected $fillable = [
        'title_ru',
        'title_uz',
        'body_ru',
        'body_uz',
        'image_path',
        'link_url',
        'published_at',
        'target_all',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'target_all' => 'boolean',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'feature_announcement_role');
    }

    public function seenByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'feature_announcement_user')
            ->withPivot('seen_at');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->whereNotNull('published_at')->where('published_at', '<=', now());
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $query->published()->where(function (Builder $q) use ($user) {
            $q->where('target_all', true)
              ->orWhereHas('roles', fn (Builder $r) => $r->where('roles.id', $user->role_id));
        });
    }

    public function title(): ?string
    {
        $locale = app()->getLocale();
        return $this->{"title_{$locale}"} ?? $this->title_ru;
    }

    public function body(): ?string
    {
        $locale = app()->getLocale();
        return $this->{"body_{$locale}"} ?? $this->body_ru;
    }
}
