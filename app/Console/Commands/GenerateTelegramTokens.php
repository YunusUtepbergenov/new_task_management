<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class GenerateTelegramTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram:generate-tokens';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Telegram tokens for users';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $users = User::whereNull('telegram_token')->get();

        foreach ($users as $user) {
            $user->telegram_token = Str::random(32);
            $user->save();
        }

        $this->info("Telegram tokens generated for " . $users->count() . " users.");
    }
}
