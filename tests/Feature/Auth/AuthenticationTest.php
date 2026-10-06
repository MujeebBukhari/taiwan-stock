<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_authenticated_user_can_access_protected_routes(): void
    {
        $user = User::factory()->create();

        $protectedRoutes = ['/dashboard', '/stocks', '/transactions', '/portfolio', '/reports', '/settings'];

        foreach ($protectedRoutes as $route) {
            $response = $this->actingAs($user)->get($route);
            $response->assertStatus(200);
        }
    }

    public function test_guest_cannot_access_protected_routes(): void
    {
        $protectedRoutes = ['/dashboard', '/stocks', '/transactions', '/portfolio', '/reports', '/settings'];

        foreach ($protectedRoutes as $route) {
            $response = $this->get($route);
            $response->assertRedirect('/login');
        }
    }

    public function test_remember_me_functionality(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
            'remember' => 'on',
        ]);

        $this->assertAuthenticated();
        $response->assertCookie(auth()->guard()->getRecallerName());
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    public function test_authenticated_users_are_redirected_away_from_login_screen(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_login_validation_requires_email_and_password(): void
    {
        $response = $this->post('/login', [
            'email' => '',
            'password' => '',
        ]);

        $response->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest();
    }

    public function test_inactive_user_cannot_authenticate(): void
    {
        $user = User::factory()->inactive()->create([
            'email' => 'inactive.login@example.com',
            'password' => 'password',
        ]);

        $response = $this->post('/login', [
            'email' => 'inactive.login@example.com',
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors(['email']);
    }

    public function test_registration_route_remains_unavailable(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(404);
    }

    public function test_login_page_does_not_expose_registration_calls_to_action(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertDontSee('Register');
        $response->assertDontSee('Create Account');
        $response->assertDontSee('Sign Up');
    }

    public function test_login_page_renders_stocksystem_branding_and_features(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('StockSystem');
        $response->assertSee('Portfolio & Ledger');
        $response->assertSee('Weighted Average Cost');
        $response->assertSee('showPassword');
        $response->assertSee('Remember me');
    }
}
