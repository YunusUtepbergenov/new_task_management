<?php

namespace App\Livewire;

use App\Models\FeatureAnnouncement;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class FeatureAnnouncements extends Component
{
    public function dismiss(): void
    {
        $user = Auth::user();
        if (! $user) {
            return;
        }

        $unseenIds = $this->unseenQuery()->pluck('id');

        if ($unseenIds->isEmpty()) {
            return;
        }

        $now = now();
        $rows = $unseenIds->map(fn ($id) => [
            'feature_announcement_id' => $id,
            'user_id' => $user->id,
            'seen_at' => $now,
        ])->all();

        DB::table('feature_announcement_user')->upsert(
            $rows,
            ['feature_announcement_id', 'user_id'],
            ['seen_at']
        );
    }

    public function render(): View
    {
        $unseen = Auth::check() ? $this->unseenQuery()->get() : collect();

        return view('livewire.feature-announcements', [
            'unseen' => $unseen,
            'hasUnseen' => $unseen->isNotEmpty(),
        ]);
    }

    private function unseenQuery()
    {
        $user = Auth::user();

        return FeatureAnnouncement::visibleTo($user)
            ->whereDoesntHave('seenByUsers', fn ($q) => $q->where('users.id', $user->id))
            ->latest('published_at');
    }
}
