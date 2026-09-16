<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordExpiryTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_with_expired_password_is_redirected_to_settings(): void
    {
        $this->seed();

        $user = User::first();
        $user->forceFill(['password_changed_at' => now()->subMonths(4)])->save();
        $this->actingAs($user);

        $this->get('/')->assertRedirect(route('settings'));
    }

    public function test_user_with_null_password_changed_at_is_treated_as_expired(): void
    {
        $this->seed();

        $user = User::first();
        $user->forceFill(['password_changed_at' => null])->save();
        $this->actingAs($user);

        $this->get('/')->assertRedirect(route('settings'));
    }

    public function test_expired_user_can_still_reach_settings_to_change_password(): void
    {
        $this->seed();

        $user = User::first();
        $user->forceFill(['password_changed_at' => now()->subMonths(4)])->save();
        $this->actingAs($user);

        $this->get(route('settings'))->assertOk();
    }

    public function test_expired_user_livewire_requests_are_not_redirected(): void
    {
        $this->seed();

        $user = User::first();
        $user->forceFill(['password_changed_at' => now()->subMonths(4)])->save();
        $this->actingAs($user);

        // Livewire 4 serves its update endpoint under a hashed prefix (livewire-{hash}/update),
        // so the middleware must recognise it by route name or the settings form can never submit.
        $response = $this->postJson(route('default-livewire.update'), [], ['X-Livewire' => '1']);

        $this->assertNotSame(302, $response->getStatusCode(), 'Livewire update request was redirected by the password-expiry middleware.');
    }

    public function test_user_with_recent_password_can_access_the_app(): void
    {
        $this->seed();

        $user = User::where('role_id', 2)->first();
        $user->forceFill(['password_changed_at' => now()])->save();
        $this->actingAs($user);

        $this->get('/')->assertOk();
    }
}
