<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithFileUploads;

class Settings extends Component
{
    use WithFileUploads;

    public $avatar;
    public $avatarPreview;
    public $oldPassword, $newPassword, $confirmPassword;

    public function mount(): void
    {
        $this->avatarPreview = Auth::user()->avatar
            ? asset('user_image/' . Auth::user()->avatar)
            : asset('user_image/avatar.jpg');
    }

    public function updatedAvatar(): void
    {
        $this->validate([
            'avatar' => 'image|max:5120|mimes:jpg,jpeg,png',
        ]);
    }

    public function saveAvatar(): void
    {
        $this->validate([
            'avatar' => 'required|image|max:5120|mimes:jpg,jpeg,png',
        ]);

        $user = Auth::user();

        if ($user->avatar) {
            $oldPath = public_path('user_image/' . $user->avatar);
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
        }

        $filename = 'UIMG' . date('Ymd') . uniqid() . '.jpg';
        $this->avatar->move(public_path('user_image'), $filename);

        $user->update(['avatar' => $filename]);

        $this->avatarPreview = asset('user_image/' . $filename) . '?v=' . time();
        $this->avatar = null;

        $this->dispatch('success', msg: 'Фото профиля успешно изменено.');
    }

    public function removeAvatar(): void
    {
        $user = Auth::user();

        if ($user->avatar) {
            $oldPath = public_path('user_image/' . $user->avatar);
            if (file_exists($oldPath)) {
                unlink($oldPath);
            }
            $user->update(['avatar' => null]);
        }

        $this->avatarPreview = asset('user_image/avatar.jpg');
        $this->avatar = null;

        $this->dispatch('success', msg: 'Фото профиля удалено.');
    }

    public function updatePassword(): void
    {
        $this->validate([
            'oldPassword' => 'required|min:6|max:20',
            'newPassword' => 'required|min:6|max:20',
            'confirmPassword' => 'required|same:newPassword',
        ]);

        if (Hash::check($this->oldPassword, Auth::user()->password)) {
            Auth::user()->update(['password' => bcrypt($this->newPassword)]);
            $this->reset(['oldPassword', 'newPassword', 'confirmPassword']);
            $this->dispatch('success', msg: 'Пароль успешно изменен.');
        } else {
            $this->addError('oldPassword', 'Неправильный пароль.');
        }
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.settings');
    }
}
