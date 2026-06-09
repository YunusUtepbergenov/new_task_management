<?php

namespace App\Console\Commands;

use App\Exports\UsersPasswordExport;
use App\Mail\TemporaryPasswordMail;
use App\Models\User;
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
        {--skip-email= : Comma-separated emails to exclude from emailing (still reset and included in the Excel)}
        {--dry-run : List affected users without changing anything}';

    protected $description = 'Reset every active user password to a strong random one and export the new credentials to an Excel file for distribution';

    public function handle(): int
    {
        $users = User::where('leave', 0)->orderBy('name')->get();

        if ($users->isEmpty()) {
            $this->warn('No active users found.');

            return self::SUCCESS;
        }

        $shouldEmail = $this->option('email');
        $skipEmail = $this->skippedEmails();

        if ($this->option('dry-run')) {
            $action = $shouldEmail ? 'would be reset and emailed' : 'would be reset';
            $this->info("Dry run — {$users->count()} active user password(s) {$action}. No changes made.");

            if ($shouldEmail) {
                $this->table(['Name', 'Email', 'Will email'], $users->map(fn (User $user) => [
                    $user->name,
                    $user->email,
                    $this->willEmail($user, $skipEmail) ? 'yes' : 'no',
                ])->all());
            } else {
                $this->table(['Name', 'Email'], $users->map(fn (User $user) => [$user->name, $user->email])->all());
            }

            $this->warnUnmatchedSkips($users, $skipEmail);

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

        // Store the Excel BEFORE sending any email. This is the source of truth, so it
        // must exist even if the email step later fails — nobody's password is ever lost.
        $path = 'passwords/user-passwords-'.now()->format('Y-m-d_His').'.xlsx';
        Excel::store(new UsersPasswordExport($rows), $path, 'local');

        $this->info("Reset {$rows->count()} active user password(s).");
        $this->line('Credentials saved to: '.Storage::disk('local')->path($path));

        // Second pass: email the new passwords (only when requested).
        if ($shouldEmail) {
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

            $this->warnUnmatchedSkips($users, $skipEmail);
        }

        $this->warn('The Excel file contains plaintext passwords. Share it securely and delete it afterwards.');

        return self::SUCCESS;
    }

    /**
     * Emails passed to --skip-email, normalized to lowercase.
     *
     * @return Collection<int, string>
     */
    private function skippedEmails(): Collection
    {
        return collect(explode(',', (string) $this->option('skip-email')))
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
     * Warn about --skip-email addresses that do not match any active user (likely a typo).
     *
     * @param  Collection<int, User>  $users
     * @param  Collection<int, string>  $skipEmail
     */
    private function warnUnmatchedSkips(Collection $users, Collection $skipEmail): void
    {
        if ($skipEmail->isEmpty()) {
            return;
        }

        $activeEmails = $users->pluck('email')->filter()->map(fn (string $email) => Str::lower($email));
        $unmatched = $skipEmail->diff($activeEmails);

        if ($unmatched->isNotEmpty()) {
            $this->warn('These --skip-email addresses did not match any active user: '.$unmatched->implode(', '));
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
