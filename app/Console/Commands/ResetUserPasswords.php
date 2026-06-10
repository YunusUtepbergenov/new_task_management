<?php

namespace App\Console\Commands;

use App\Exports\UsersPasswordExport;
use App\Mail\TemporaryPasswordMail;
use App\Models\User;
use App\Services\TelegramBotService;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class ResetUserPasswords extends Command
{
    protected $signature = 'users:reset-passwords
        {--email : Email each user their new password (localized by the user\'s locale)}
        {--telegram : Send each user their new password via the Telegram bot (localized by the user\'s locale)}
        {--skip-email= : Comma-separated emails to exclude from emailing (still reset and included in the Excel)}
        {--skip-telegram= : Comma-separated emails to exclude from Telegram (still reset and included in the Excel)}
        {--dry-run : List affected users without changing anything}';

    protected $description = 'Reset every active user password to a strong random one and export the new credentials to an Excel file for distribution';

    public function handle(TelegramBotService $telegram): int
    {
        $users = User::where('leave', 0)->orderBy('name')->get();

        if ($users->isEmpty()) {
            $this->warn('No active users found.');

            return self::SUCCESS;
        }

        $shouldEmail = $this->option('email');
        $shouldTelegram = $this->option('telegram');
        $skipEmail = $this->parseSkipList('skip-email');
        $skipTelegram = $this->parseSkipList('skip-telegram');

        if ($this->option('dry-run')) {
            $this->previewDryRun($users, $shouldEmail, $shouldTelegram, $skipEmail, $skipTelegram);

            return self::SUCCESS;
        }

        // First pass: reset every active user's password and collect the credentials.
        $rows = new Collection();
        $credentials = [];

        foreach ($users as $user) {
            $password = $this->generatePassword();

            $user->forceFill([
                'password' => Hash::make($password),
                'password_changed_at' => now(),
            ])->save();

            $rows->push([
                'name' => $user->name,
                'email' => $user->email,
                'password' => $password,
            ]);

            $credentials[] = ['user' => $user, 'password' => $password];
        }

        // Store the Excel BEFORE sending anything. This is the source of truth, so it must
        // exist even if a delivery step later fails — nobody's password is ever lost.
        $path = 'passwords/user-passwords-'.now()->format('Y-m-d_His').'.xlsx';
        Excel::store(new UsersPasswordExport($rows), $path, 'local');

        $this->info("Reset {$rows->count()} active user password(s).");
        $this->line('Credentials saved to: '.Storage::disk('local')->path($path));

        if ($shouldEmail) {
            $this->deliverByEmail($credentials, $skipEmail);
            $this->warnUnmatchedSkips($users, $skipEmail, '--skip-email');
        }

        if ($shouldTelegram) {
            $this->deliverByTelegram($telegram, $credentials, $skipTelegram);
            $this->warnUnmatchedSkips($users, $skipTelegram, '--skip-telegram');
        }

        $this->warn('The Excel file contains plaintext passwords. Share it securely and delete it afterwards.');

        return self::SUCCESS;
    }

    /**
     * @param  array<int, array{user: User, password: string}>  $credentials
     * @param  Collection<int, string>  $skipEmail
     */
    private function deliverByEmail(array $credentials, Collection $skipEmail): void
    {
        $emailed = 0;
        $skipped = 0;
        $failed = [];

        foreach ($credentials as ['user' => $user, 'password' => $password]) {
            if (! $this->willEmail($user, $skipEmail)) {
                $skipped++;

                continue;
            }

            try {
                Mail::to($user->email)->send(
                    (new TemporaryPasswordMail($user->name, $password))->locale($user->locale ?? 'ru')
                );
                $emailed++;
            } catch (Throwable $e) {
                $failed[] = $user->email;
                $this->warn("Failed to email {$user->email}: {$e->getMessage()}");
            }
        }

        $this->info("Emailed {$emailed} user(s).");

        if ($skipped > 0) {
            $this->line("Skipped email for {$skipped} user(s) — distribute their passwords from the Excel file.");
        }

        if (! empty($failed)) {
            $this->warn(count($failed).' email(s) failed — distribute these from the Excel file: '.implode(', ', $failed));
        }
    }

    /**
     * @param  array<int, array{user: User, password: string}>  $credentials
     * @param  Collection<int, string>  $skipTelegram
     */
    private function deliverByTelegram(TelegramBotService $telegram, array $credentials, Collection $skipTelegram): void
    {
        $sent = 0;
        $skipped = 0;
        $failed = [];

        foreach ($credentials as ['user' => $user, 'password' => $password]) {
            if (! $this->willTelegram($user, $skipTelegram)) {
                $skipped++;

                continue;
            }

            if ($telegram->sendMessage($user->telegram_chat_id, $this->telegramMessage($user, $password))) {
                $sent++;
            } else {
                $failed[] = $user->email ?: (string) $user->telegram_chat_id;
            }
        }

        $this->info("Sent {$sent} Telegram message(s).");

        if ($skipped > 0) {
            $this->line("Skipped Telegram for {$skipped} user(s) (no linked chat or excluded) — distribute from the Excel file.");
        }

        if (! empty($failed)) {
            $this->warn(count($failed).' Telegram message(s) failed — distribute these from the Excel file: '.implode(', ', $failed));
        }
    }

    /**
     * Build the localized Telegram message. Dynamic values are HTML-escaped because the
     * bot sends with parse_mode=HTML.
     */
    private function telegramMessage(User $user, string $password): string
    {
        $locale = $user->locale ?? 'ru';

        return '<b>'.e(__('emails.temp_password.greeting', ['name' => $user->name], $locale)).'</b>'."\n\n"
            .e(__('emails.temp_password.intro', [], $locale))."\n"
            .'<code>'.e($password).'</code>'."\n\n"
            .e(__('emails.temp_password.advice', [], $locale))."\n"
            .e(__('emails.temp_password.expiry', [], $locale));
    }

    /**
     * @param  Collection<int, User>  $users
     * @param  Collection<int, string>  $skipEmail
     * @param  Collection<int, string>  $skipTelegram
     */
    private function previewDryRun(Collection $users, bool $shouldEmail, bool $shouldTelegram, Collection $skipEmail, Collection $skipTelegram): void
    {
        $channels = [];
        if ($shouldEmail) {
            $channels[] = 'emailed';
        }
        if ($shouldTelegram) {
            $channels[] = 'sent via Telegram';
        }
        $action = empty($channels) ? 'would be reset' : 'would be reset and '.implode(' / ', $channels);

        $this->info("Dry run — {$users->count()} active user password(s) {$action}. No changes made.");

        $headers = ['Name', 'Email'];
        if ($shouldEmail) {
            $headers[] = 'Will email';
        }
        if ($shouldTelegram) {
            $headers[] = 'Will Telegram';
        }

        $this->table($headers, $users->map(function (User $user) use ($shouldEmail, $shouldTelegram, $skipEmail, $skipTelegram) {
            $row = [$user->name, $user->email];
            if ($shouldEmail) {
                $row[] = $this->willEmail($user, $skipEmail) ? 'yes' : 'no';
            }
            if ($shouldTelegram) {
                $row[] = $this->willTelegram($user, $skipTelegram) ? 'yes' : 'no';
            }

            return $row;
        })->all());

        if ($shouldEmail) {
            $this->warnUnmatchedSkips($users, $skipEmail, '--skip-email');
        }
        if ($shouldTelegram) {
            $this->warnUnmatchedSkips($users, $skipTelegram, '--skip-telegram');
        }
    }

    /**
     * Parse a comma-separated email option into a normalized (lowercase) collection.
     *
     * @return Collection<int, string>
     */
    private function parseSkipList(string $option): Collection
    {
        return collect(explode(',', (string) $this->option($option)))
            ->map(fn (string $email) => Str::lower(trim($email)))
            ->filter()
            ->values();
    }

    /**
     * @param  Collection<int, string>  $skipEmail
     */
    private function willEmail(User $user, Collection $skipEmail): bool
    {
        return filled($user->email) && ! $skipEmail->contains(Str::lower($user->email));
    }

    /**
     * @param  Collection<int, string>  $skipTelegram
     */
    private function willTelegram(User $user, Collection $skipTelegram): bool
    {
        return filled($user->telegram_chat_id)
            && ! $skipTelegram->contains(Str::lower((string) $user->email));
    }

    /**
     * Warn about skip addresses that do not match any active user (likely a typo).
     *
     * @param  Collection<int, User>  $users
     * @param  Collection<int, string>  $skipList
     */
    private function warnUnmatchedSkips(Collection $users, Collection $skipList, string $optionLabel): void
    {
        if ($skipList->isEmpty()) {
            return;
        }

        $activeEmails = $users->pluck('email')->filter()->map(fn (string $email) => Str::lower($email));
        $unmatched = $skipList->diff($activeEmails);

        if ($unmatched->isNotEmpty()) {
            $this->warn("These {$optionLabel} addresses did not match any active user: ".$unmatched->implode(', '));
        }
    }

    /**
     * Generate a 10-character password that satisfies the application password policy
     * (uppercase, lowercase, number and special character).
     */
    private function generatePassword(): string
    {
        do {
            $password = Str::password(10);
        } while (
            ! preg_match('/[A-Z]/', $password)
            || ! preg_match('/[a-z]/', $password)
            || ! preg_match('/\d/', $password)
            || ! preg_match('/[^A-Za-z0-9]/', $password)
        );

        return $password;
    }
}
