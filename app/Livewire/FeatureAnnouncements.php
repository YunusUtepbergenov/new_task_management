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

        $unseenIds = FeatureAnnouncement::visibleTo($user)
            ->whereDoesntHave('seenByUsers', fn ($q) => $q->where('users.id', $user->id))
            ->pluck('id');

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
        $user = Auth::user();

        if (! $user) {
            return view('livewire.feature-announcements', [
                'history' => collect(),
                'unseenCount' => 0,
                'hasUnseen' => false,
            ]);
        }

        $history = FeatureAnnouncement::visibleTo($user)
            ->with(['seenByUsers' => fn ($q) => $q->where('users.id', $user->id)])
            ->latest('published_at')
            ->limit(30)
            ->get();

        $unseenCount = $history->filter(fn ($a) => $a->seenByUsers->isEmpty())->count();

        return view('livewire.feature-announcements', [
            'history' => $history,
            'unseenCount' => $unseenCount,
            'hasUnseen' => $unseenCount > 0,
        ]);
    }
}
