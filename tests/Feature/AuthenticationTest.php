<?php

namespace Tests\Feature;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Log;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('css/auth-login.css', false);
        $response->assertDontSee('css/login.css', false);
    }

    public function test_users_can_authenticate_using_the_login_screen()
    {

        $this->seed();

        $response = $this->post('/login', [
            'email' => 'y.utepbergenov@cerr.uz',
            'password' => 'yu3667500',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(RouteServiceProvider::HOME);
    }

    public function test_users_can_not_authenticate_with_invalid_password()
    {
        $this->seed();
        
        $this->post('/login', [
            'email' => 'y.utepbergenov@cerr.uz',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }
}
