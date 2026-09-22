<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TelegramUserTasksApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        config(['services.telegram.api_secret' => 'api-secret']);

        $this->user = User::factory()->create(['role_id' => 3, 'sector_id' => 1, 'telegram_chat_id' => 123456]);

        Task::factory()->create([
            'user_id' => $this->user->id,
            'sector_id' => 1,
            'project_id' => null,
            'score_id' => null,
            'status' => 'Выполняется',
            'name' => 'Private task',
        ]);
    }

    public function test_request_without_secret_is_rejected(): void
    {
        $this->postJson('/api/telegram/get-user-tasks', ['chat_id' => 123456])
            ->assertForbidden()
            ->assertJsonMissing(['name' => 'Private task']);
    }

    public function test_request_with_wrong_secret_is_rejected(): void
    {
        $this->postJson('/api/telegram/get-user-tasks', ['chat_id' => 123456], [
            'X-Telegram-Api-Secret' => 'wrong-secret',
        ])->assertForbidden();
    }

    public function test_requests_are_rejected_when_no_secret_is_configured(): void
    {
        config(['services.telegram.api_secret' => null]);

        $this->postJson('/api/telegram/get-user-tasks', ['chat_id' => 123456], [
            'X-Telegram-Api-Secret' => '',
        ])->assertForbidden();
    }

    public function test_request_with_valid_secret_returns_the_users_tasks(): void
    {
        $this->postJson('/api/telegram/get-user-tasks', ['chat_id' => 123456], [
            'X-Telegram-Api-Secret' => 'api-secret',
        ])
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('tasks.0.name', 'Private task');
    }

    public function test_unknown_chat_id_returns_not_found_with_valid_secret(): void
    {
        $this->postJson('/api/telegram/get-user-tasks', ['chat_id' => 999], [
            'X-Telegram-Api-Secret' => 'api-secret',
        ])->assertNotFound();
    }
}
