<?php

namespace Tests\Feature;

use App\Livewire\Admin\ManageFeatureAnnouncements;
use App\Livewire\FeatureAnnouncements as FeatureAnnouncementsComponent;
use App\Models\FeatureAnnouncement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class FeatureAnnouncementsTest extends TestCase
{
    use RefreshDatabase;

    private function user(int $roleId = 3): User
    {
        return User::factory()->create(['role_id' => $roleId, 'sector_id' => 1]);
    }

    public function test_target_all_announcement_is_visible_to_any_user(): void
    {
        $this->seed();
        $user = $this->user();

        $announcement = FeatureAnnouncement::create([
            'title_ru' => 'Hello',
            'body_ru' => 'Body',
            'published_at' => now(),
            'target_all' => true,
        ]);

        Livewire::actingAs($user)
            ->test(FeatureAnnouncementsComponent::class)
            ->assertViewHas('hasUnseen', true)
            ->assertViewHas('unseen', fn ($unseen) => $unseen->contains('id', $announcement->id));
    }

    public function test_role_targeted_announcement_is_hidden_from_other_roles(): void
    {
        $this->seed();
        $matching = $this->user(roleId: 14);
        $other = $this->user(roleId: 3);

        $announcement = FeatureAnnouncement::create([
            'title_ru' => 'Deputies only',
            'body_ru' => 'Body',
            'published_at' => now(),
            'target_all' => false,
        ]);
        $announcement->roles()->sync([14]);

        Livewire::actingAs($matching)
            ->test(FeatureAnnouncementsComponent::class)
            ->assertViewHas('hasUnseen', true);

        Livewire::actingAs($other)
            ->test(FeatureAnnouncementsComponent::class)
            ->assertViewHas('hasUnseen', false);
    }

    public function test_drafts_are_not_visible(): void
    {
        $this->seed();
        $user = $this->user();

        FeatureAnnouncement::create([
            'title_ru' => 'Draft',
            'body_ru' => 'Body',
            'published_at' => null,
            'target_all' => true,
        ]);

        Livewire::actingAs($user)
            ->test(FeatureAnnouncementsComponent::class)
            ->assertViewHas('hasUnseen', false);
    }

    public function test_dismiss_marks_all_visible_unseen_as_seen(): void
    {
        $this->seed();
        $user = $this->user();

        $announcement = FeatureAnnouncement::create([
            'title_ru' => 'Hi',
            'body_ru' => 'Body',
            'published_at' => now(),
            'target_all' => true,
        ]);

        Livewire::actingAs($user)
            ->test(FeatureAnnouncementsComponent::class)
            ->call('dismiss')
            ->assertViewHas('hasUnseen', false);

        $this->assertDatabaseHas('feature_announcement_user', [
            'feature_announcement_id' => $announcement->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_admin_page_forbidden_for_non_admin(): void
    {
        $this->seed();
        $user = $this->user();

        $this->actingAs($user)
            ->get('/admin/announcements')
            ->assertForbidden();
    }

    public function test_admin_page_allowed_for_user_id_30(): void
    {
        $this->seed();

        // Create enough factory users so the 30th id exists and is our caller.
        while (User::max('id') < 30) {
            User::factory()->create(['role_id' => 3, 'sector_id' => 1]);
        }
        $admin = User::find(30);

        $this->actingAs($admin)
            ->get('/admin/announcements')
            ->assertOk();
    }

    public function test_admin_can_create_announcement(): void
    {
        $this->seed();

        while (User::max('id') < 30) {
            User::factory()->create(['role_id' => 3, 'sector_id' => 1]);
        }
        $admin = User::find(30);

        Livewire::actingAs($admin)
            ->test(ManageFeatureAnnouncements::class)
            ->set('title_ru', 'New feature')
            ->set('body_ru', 'Markdown body')
            ->set('target_all', true)
            ->set('publish', true)
            ->call('save');

        $this->assertDatabaseHas('feature_announcements', [
            'title_ru' => 'New feature',
            'target_all' => true,
        ]);
        $this->assertNotNull(FeatureAnnouncement::where('title_ru', 'New feature')->value('published_at'));
    }
}
