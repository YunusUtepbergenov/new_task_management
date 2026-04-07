<?php

namespace App\Livewire;

use App\Models\DirectMessage;
use App\Models\User;
use App\Services\DirectMessageService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class DirectMessages extends Component
{
    use WithPagination;

    public string $messageText = '';
    public array $selectedUserIds = [];

    public function mount(): void
    {
        if (!Auth::user()->isDeputy() && !Auth::user()->isDirector()) {
            abort(403);
        }
    }

    public function sendMessage(DirectMessageService $service): void
    {
        $this->validate([
            'messageText' => 'required|string|min:1|max:4000',
            'selectedUserIds' => 'required|array|min:1',
            'selectedUserIds.*' => 'exists:users,id',
        ], [
            'messageText.required' => 'Введите текст сообщения.',
            'selectedUserIds.required' => 'Выберите хотя бы одного получателя.',
            'selectedUserIds.min' => 'Выберите хотя бы одного получателя.',
        ]);

        $service->send(
            Auth::user(),
            $this->selectedUserIds,
            $this->messageText,
            'web'
        );

        $this->reset(['messageText', 'selectedUserIds']);
        $this->dispatch('message-sent');
        session()->flash('success', 'Сообщение успешно отправлено!');
    }

    public function render()
    {
        $availableUsers = User::whereNotNull('telegram_chat_id')
            ->where('id', '!=', Auth::id())
            ->with('sector')
            ->orderBy('name')
            ->get();

        $sentMessages = DirectMessage::where('sender_id', Auth::id())
            ->with('recipients')
            ->latest()
            ->paginate(10);

        return view('livewire.direct-messages', [
            'availableUsers' => $availableUsers,
            'sentMessages' => $sentMessages,
        ]);
    }
}
