<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class Settings extends Component
{
    use WithFileUploads;

    public $avatar;
    public $avatarPreview;
    public $avatarDataUrl;
    public $oldPassword, $newPassword, $confirmPassword;
    public $phone, $internal;
    public $telegramToken = null;
    public $telegramLinked = false;
    public string $locale = 'ru';

    public function mount(): void
    {
        $this->avatarPreview = Auth::user()->avatar
            ? asset('user_image/' . Auth::user()->avatar)
            : asset('user_image/avatar.jpg');

        $this->phone = Auth::user()->phone;
        $this->internal = Auth::user()->internal;
        $this->telegramLinked = (bool) Auth::user()->telegram_chat_id;
        $this->locale = Auth::user()->locale ?? 'ru';
    }

    public function updatedAvatar(): void
    {
        if (!$this->avatar) {
            $this->avatarDataUrl = null;
            return;
        }

        $this->validate([
            'avatar' => 'image|max:5120|mimes:jpg,jpeg,png',
        ]);

        $this->avatarDataUrl = 'data:' . $this->avatar->getMimeType() . ';base64,' . base64_encode(file_get_contents($this->avatar->getRealPath()));
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
        $destination = public_path('user_image' . DIRECTORY_SEPARATOR . $filename);
        copy($this->avatar->getRealPath(), $destination);

        $user->update(['avatar' => $filename]);

        $this->avatarPreview = asset('user_image/' . $filename) . '?v=' . time();
        $this->avatarDataUrl = null;
        $this->avatar = null;

        $this->dispatch('success', msg: __('notifications.avatar_updated'));
        $this->dispatch('avatar-updated', url: $this->avatarPreview);
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
        $this->avatarDataUrl = null;
        $this->avatar = null;

        $this->dispatch('success', msg: __('notifications.avatar_removed'));
        $this->dispatch('avatar-updated', url: $this->avatarPreview);
    }

    public function updateContactInfo(): void
    {
        $this->validate([
            'phone' => 'required|min:9',
            'internal' => 'nullable|max:3',
        ]);

        $user = Auth::user();
        $user->phone = $this->phone;
        $user->internal = $this->internal;
        $user->save();

        $this->dispatch('success', msg: __('notifications.contact_updated'));
    }

    public function updatePassword(): void
    {
        // Mirrors the application password policy (see AppServiceProvider::boot),
        // but reports a single human-readable, localized message instead of the
        // framework's raw "validation.password.*" keys.
        $this->validate([
            'oldPassword' => 'required|string',
            'newPassword' => ['bail', 'required', 'string', 'min:8', 'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).+$/'],
            'confirmPassword' => 'required|same:newPassword',
        ], [
            'oldPassword.required' => __('settings.old_password_required'),
            'newPassword.required' => __('settings.password_required'),
            'newPassword.min' => __('settings.password_requirements'),
            'newPassword.regex' => __('settings.password_requirements'),
            'confirmPassword.required' => __('settings.password_confirm_required'),
            'confirmPassword.same' => __('settings.password_mismatch'),
        ]);

        if (Hash::check($this->oldPassword, Auth::user()->password)) {
            Auth::user()->forceFill([
                'password' => bcrypt($this->newPassword),
                'password_changed_at' => now(),
            ])->save();
            $this->reset(['oldPassword', 'newPassword', 'confirmPassword']);
            $this->dispatch('success', msg: __('notifications.password_changed'));
        } else {
            $this->addError('oldPassword', __('notifications.wrong_password'));
        }
    }

    public function generateTelegramToken(): void
    {
        $plainToken = Str::random(32);

        Auth::user()->update([
            'telegram_token' => hash('sha256', $plainToken),
            'telegram_token_expires_at' => now()->addMinutes(10),
        ]);

        $this->telegramToken = $plainToken;
        $this->dispatch('success', msg: __('notifications.token_generated'));
    }

    public function updateLocale(): void
    {
        $this->validate(['locale' => 'required|in:ru,uz']);

        Auth::user()->update(['locale' => $this->locale]);
        app()->setLocale($this->locale);

        $this->dispatch('success', msg: __('notifications.locale_updated'));
    }

    public function unlinkTelegram(): void
    {
        Auth::user()->update([
            'telegram_chat_id' => null,
            'telegram_token' => null,
            'telegram_token_expires_at' => null,
        ]);

        $this->telegramLinked = false;
        $this->telegramToken = null;
        $this->dispatch('success', msg: __('notifications.telegram_unlinked'));
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.settings');
    }
}
