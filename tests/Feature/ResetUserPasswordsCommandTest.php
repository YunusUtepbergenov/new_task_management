<?php

namespace Tests\Feature;

use App\Exports\UsersPasswordExport;
use App\Mail\TemporaryPasswordMail;
use App\Models\User;
use App\Services\TelegramBotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class ResetUserPasswordsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_resets_all_passwords_and_exports_them_to_excel(): void
    {
        $this->seed();
        Excel::fake();
        $this->travelTo(now());

        $user = User::first();
        $this->assertTrue(Hash::check('password', $user->password));

        $expectedPath = 'passwords/user-passwords-'.now()->format('Y-m-d_His').'.xlsx';

        $this->artisan('users:reset-passwords')->assertSuccessful();

        $user->refresh();
        $this->assertFalse(Hash::check('password', $user->password));
        $this->assertNotNull($user->password_changed_at);
        $this->assertTrue($user->password_changed_at->isToday());

        Excel::assertStored($expectedPath, 'local', function (UsersPasswordExport $export) {
            foreach ($export->collection() as $row) {
                $password = $row['password'];
                $this->assertSame(10, strlen($password));
                $this->assertMatchesRegularExpression('/[A-Z]/', $password);
                $this->assertMatchesRegularExpression('/[a-z]/', $password);
                $this->assertMatchesRegularExpression('/\d/', $password);
                $this->assertMatchesRegularExpression('/[^A-Za-z0-9]/', $password);
            }

            return $export->collection()->isNotEmpty();
        });
    }

    public function test_left_users_are_not_reset(): void
    {
        $this->seed();
        Excel::fake();

        $active = User::where('leave', 0)->orderBy('id')->first();
        $left = User::where('leave', 0)->orderBy('id')->skip(1)->first();
        $left->forceFill(['leave' => 1])->save();

        $this->artisan('users:reset-passwords')->assertSuccessful();

        $active->refresh();
        $left->refresh();

        $this->assertFalse(Hash::check('password', $active->password));
        $this->assertTrue(Hash::check('password', $left->password));
    }

    public function test_passwords_are_emailed_to_active_users_when_flag_is_set(): void
    {
        $this->seed();
        Excel::fake();
        Mail::fake();

        $active = User::where('leave', 0)->orderBy('id')->first();
        $left = User::where('leave', 0)->orderBy('id')->skip(1)->first();
        $left->forceFill(['leave' => 1])->save();

        $this->artisan('users:reset-passwords --email')->assertSuccessful();

        Mail::assertSent(TemporaryPasswordMail::class, fn (TemporaryPasswordMail $mail) => $mail->hasTo($active->email));
        Mail::assertNotSent(TemporaryPasswordMail::class, fn (TemporaryPasswordMail $mail) => $mail->hasTo($left->email));
    }

    public function test_skip_email_users_are_reset_but_not_emailed(): void
    {
        $this->seed();
        Excel::fake();
        Mail::fake();

        $skipped = User::where('leave', 0)->orderBy('id')->first();
        $emailedUser = User::where('leave', 0)->orderBy('id')->skip(1)->first();

        $this->artisan('users:reset-passwords --email --skip-email='.$skipped->email)->assertSuccessful();

        // Skipped user still gets a new password, but receives no email.
        $skipped->refresh();
        $this->assertFalse(Hash::check('password', $skipped->password));
        Mail::assertNotSent(TemporaryPasswordMail::class, fn (TemporaryPasswordMail $mail) => $mail->hasTo($skipped->email));

        // Everyone else is emailed.
        Mail::assertSent(TemporaryPasswordMail::class, fn (TemporaryPasswordMail $mail) => $mail->hasTo($emailedUser->email));
    }

    public function test_passwords_are_not_emailed_without_the_flag(): void
    {
        $this->seed();
        Excel::fake();
        Mail::fake();

        $this->artisan('users:reset-passwords')->assertSuccessful();

        Mail::assertNothingSent();
    }

    public function test_passwords_are_sent_via_telegram_to_linked_users(): void
    {
        $this->seed();
        Excel::fake();

        $linked = User::where('leave', 0)->orderBy('id')->first();
        $linked->forceFill(['telegram_chat_id' => 555001])->save();

        $this->mock(TelegramBotService::class, function ($mock) {
            $mock->shouldReceive('sendMessage')
                ->once()
                ->withArgs(fn ($chatId, $text) => $chatId === 555001 && str_contains($text, '<code>') && str_contains($text, 'Здравствуйте'))
                ->andReturn(true);
        });

        $this->artisan('users:reset-passwords --telegram')->assertSuccessful();

        $linked->refresh();
        $this->assertFalse(Hash::check('password', $linked->password));
    }

    public function test_telegram_message_is_localized_to_the_user_locale(): void
    {
        $this->seed();
        Excel::fake();

        $linked = User::where('leave', 0)->orderBy('id')->first();
        $linked->forceFill(['telegram_chat_id' => 555020, 'locale' => 'uz'])->save();

        $this->mock(TelegramBotService::class, function ($mock) {
            $mock->shouldReceive('sendMessage')
                ->once()
                ->withArgs(fn ($chatId, $text) => $chatId === 555020 && str_contains($text, 'Ассалому алайкум'))
                ->andReturn(true);
        });

        $this->artisan('users:reset-passwords --telegram')->assertSuccessful();
    }

    public function test_skip_telegram_excludes_users_from_telegram(): void
    {
        $this->seed();
        Excel::fake();

        $skipped = User::where('leave', 0)->orderBy('id')->first();
        $skipped->forceFill(['telegram_chat_id' => 555010])->save();
        $delivered = User::where('leave', 0)->orderBy('id')->skip(1)->first();
        $delivered->forceFill(['telegram_chat_id' => 555011])->save();

        $this->mock(TelegramBotService::class, function ($mock) {
            $mock->shouldReceive('sendMessage')
                ->once()
                ->withArgs(fn ($chatId, $text) => $chatId === 555011)
                ->andReturn(true);
        });

        $this->artisan('users:reset-passwords --telegram --skip-telegram='.$skipped->email)->assertSuccessful();
    }

    public function test_no_telegram_sent_without_the_flag(): void
    {
        $this->seed();
        Excel::fake();

        $linked = User::where('leave', 0)->orderBy('id')->first();
        $linked->forceFill(['telegram_chat_id' => 555030])->save();

        $this->mock(TelegramBotService::class, function ($mock) {
            $mock->shouldNotReceive('sendMessage');
        });

        $this->artisan('users:reset-passwords')->assertSuccessful();
    }

    public function test_dry_run_does_not_change_passwords(): void
    {
        $this->seed();
        Excel::fake();

        $user = User::first();
        $originalHash = $user->password;

        $this->artisan('users:reset-passwords --dry-run')->assertSuccessful();

        $user->refresh();
        $this->assertSame($originalHash, $user->password);
    }
}
