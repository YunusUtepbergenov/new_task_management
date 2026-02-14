<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class Notifications extends Component
{
    #[On('dismiss-notification')]
    public function dismiss(string $id): void
    {
        Auth::user()->unreadNotifications()->where('id', $id)->update(['read_at' => now()]);
    }

    #[On('dismiss-all-notifications')]
    public function dismissAll(): void
    {
        Auth::user()->unreadNotifications()->update(['read_at' => now()]);
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.notifications', [
            'notifications' => Auth::user()->unreadNotifications()->get(),
            'count' => Auth::user()->unreadNotifications()->count(),
        ]);
    }
}
