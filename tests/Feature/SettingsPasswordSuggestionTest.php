<?php

namespace Tests\Feature;

use App\Livewire\Settings;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class SettingsPasswordSuggestionTest extends TestCase
{
    use RefreshDatabase;

    public function test_suggest_password_fills_both_new_password_fields(): void
    {
        $this->seed();

        $user = User::first();
        $this->actingAs($user);

        $component = Livewire::test(Settings::class)->call('suggestPassword');

        $suggested = $component->get('newPassword');

        $this->assertNotEmpty($suggested);
        $this->assertMatchesRegularExpression('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $suggested);

        $component
            ->assertSet('confirmPassword', $suggested)
            ->assertDispatched('password-suggested');
    }

    public function test_suggested_password_can_be_saved_as_the_new_password(): void
    {
        $this->seed();

        $user = User::first();
        $this->actingAs($user);

        $component = Livewire::test(Settings::class)->call('suggestPassword');
        $suggested = $component->get('newPassword');

        $component
            ->set('oldPassword', 'password')
            ->call('updatePassword')
            ->assertHasNoErrors()
            ->assertSet('newPassword', null)
            ->assertSet('confirmPassword', null);

        $this->assertTrue(Hash::check($suggested, $user->fresh()->password));
    }

    public function test_password_fields_render_with_show_hide_toggles(): void
    {
        $this->seed();

        $user = User::first();
        $this->actingAs($user);

        Livewire::test(Settings::class)
            ->assertSee(__('settings.suggest_password'))
            ->assertSeeHtml('class="settings-input-toggle"')
            ->assertSeeHtml('x-on:password-suggested.window');
    }
}
