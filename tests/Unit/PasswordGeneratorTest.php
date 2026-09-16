<?php

namespace Tests\Unit;

use App\Services\PasswordGenerator;
use PHPUnit\Framework\TestCase;

class PasswordGeneratorTest extends TestCase
{
    public function test_generated_password_has_requested_length(): void
    {
        $generator = new PasswordGenerator();

        $this->assertSame(12, strlen($generator->generate()));
        $this->assertSame(10, strlen($generator->generate(10)));
    }

    public function test_generated_password_satisfies_the_application_policy(): void
    {
        $generator = new PasswordGenerator();

        for ($i = 0; $i < 25; $i++) {
            $password = $generator->generate();

            $this->assertMatchesRegularExpression('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password);
        }
    }

    public function test_generated_passwords_are_unique(): void
    {
        $generator = new PasswordGenerator();

        $this->assertNotSame($generator->generate(), $generator->generate());
    }
}
