<?php

namespace Tests\Feature\Auth;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_is_disabled_by_default(): void
    {
        $response = $this->get('/register');

        $response->assertRedirect(route('login'));
    }

    public function test_registration_screen_can_be_rendered_when_enabled(): void
    {
        Setting::set('allow_registration', '1');

        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register_when_enabled(): void
    {
        Setting::set('allow_registration', '1');

        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_registration_is_rejected_when_disabled(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertForbidden();
        $this->assertGuest();
    }
}
