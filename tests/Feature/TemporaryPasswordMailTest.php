<?php

namespace Tests\Feature;

use App\Mail\TemporaryPasswordMail;
use Tests\TestCase;

class TemporaryPasswordMailTest extends TestCase
{
    public function test_email_is_localized_to_uzbek_cyrillic(): void
    {
        $mail = (new TemporaryPasswordMail('А.Иванов', 'Xy7#kPq2!m'))->locale('uz');

        $mail->assertHasSubject(__('emails.temp_password.subject', [], 'uz'));
        $mail->assertSeeInHtml('Ассалому алайкум');
        $mail->assertSeeInHtml('Xy7#kPq2!m');
    }

    public function test_email_is_localized_to_russian(): void
    {
        $mail = (new TemporaryPasswordMail('А.Иванов', 'Xy7#kPq2!m'))->locale('ru');

        $mail->assertHasSubject(__('emails.temp_password.subject', [], 'ru'));
        $mail->assertSeeInHtml('Здравствуйте');
        $mail->assertSeeInHtml('Xy7#kPq2!m');
    }
}
