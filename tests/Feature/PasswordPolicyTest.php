<?php

namespace Tests\Feature;

use App\Livewire\Settings;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class PasswordPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_weak_password_is_rejected(): void
    {
        $this->seed();
        $user = User::first();
        $this->actingAs($user);

        Livewire::test(Settings::class)
            ->set('oldPassword', 'password')
            ->set('newPassword', 'weakpass')
            ->set('confirmPassword', 'weakpass')
            ->call('updatePassword')
            ->assertHasErrors(['newPassword' => 'regex']);

        $user->refresh();
        $this->assertTrue(Hash::check('password', $user->password));
    }

    public function test_password_error_message_is_human_readable_not_a_raw_validation_key(): void
    {
        app()->setLocale('ru');
        $this->seed();
        $user = User::first();
        $this->actingAs($user);

        $component = Livewire::test(Settings::class)
            ->set('oldPassword', 'password')
            ->set('newPassword', 'weakpass')
            ->set('confirmPassword', 'weakpass')
            ->call('updatePassword');

        $message = $component->errors()->first('newPassword');
        $this->assertSame(__('settings.password_requirements'), $message);
        $this->assertStringNotContainsString('validation.', $message);
    }

    public function test_strong_password_is_accepted_and_refreshes_changed_timestamp(): void
    {
        $this->seed();
        $user = User::first();
        $user->forceFill(['password_changed_at' => now()->subMonths(4)])->save();
        $this->actingAs($user);

        Livewire::test(Settings::class)
            ->set('oldPassword', 'password')
            ->set('newPassword', 'Str0ng#Pass1')
            ->set('confirmPassword', 'Str0ng#Pass1')
            ->call('updatePassword')
            ->assertHasNoErrors();

        $user->refresh();
        $this->assertTrue(Hash::check('Str0ng#Pass1', $user->password));
        $this->assertTrue($user->password_changed_at->isToday());
        $this->assertFalse($user->passwordExpired());
    }

    public function test_wrong_current_password_is_rejected(): void
    {
        $this->seed();
        $user = User::first();
        $this->actingAs($user);

        Livewire::test(Settings::class)
            ->set('oldPassword', 'not-the-password')
            ->set('newPassword', 'Str0ng#Pass1')
            ->set('confirmPassword', 'Str0ng#Pass1')
            ->call('updatePassword')
            ->assertHasErrors('oldPassword');

        $user->refresh();
        $this->assertTrue(Hash::check('password', $user->password));
    }
}
