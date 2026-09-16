<?php

namespace App\Services;

use Illuminate\Support\Str;

class PasswordGenerator
{
    /**
     * Generate a random password that satisfies the application password policy
     * (uppercase, lowercase, number and special character).
     */
    public function generate(int $length = 12): string
    {
        do {
            $password = Str::password($length);
        } while (! $this->satisfiesPolicy($password));

        return $password;
    }

    private function satisfiesPolicy(string $password): bool
    {
        return preg_match('/[A-Z]/', $password) === 1
            && preg_match('/[a-z]/', $password) === 1
            && preg_match('/\d/', $password) === 1
            && preg_match('/[^A-Za-z0-9]/', $password) === 1;
    }
}
