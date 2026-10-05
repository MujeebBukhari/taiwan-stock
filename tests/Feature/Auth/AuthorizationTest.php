<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_role_is_recognized(): void
    {
        $admin = User::factory()->admin()->create();

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($admin->isUser());
        $this->assertTrue($admin->isActive());
    }

    public function test_user_role_is_recognized(): void
    {
        $user = User::factory()->user()->create();

        $this->assertTrue($user->isUser());
        $this->assertFalse($user->isAdmin());
        $this->assertTrue($user->isActive());
    }

    public function test_inactive_user_cannot_login(): void
    {
        $inactiveUser = User::factory()->inactive()->create([
            'email' => 'inactive@example.com',
            'password' => 'secret123',
        ]);

        $response = $this->post('/login', [
            'email' => 'inactive@example.com',
            'password' => 'secret123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors(['email']);
    }

    public function test_inactive_user_cannot_access_protected_application_routes(): void
    {
        $inactiveUser = User::factory()->inactive()->create();

        $response = $this->actingAs($inactiveUser)->get('/dashboard');

        $this->assertGuest();
        $response->assertRedirect('/login');
        $response->assertSessionHasErrors(['email']);
    }

    public function test_admin_gate_authorization(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->user()->create();
        $inactiveAdmin = User::factory()->admin()->inactive()->create();

        $this->assertTrue(Gate::forUser($admin)->allows('admin'));
        $this->assertTrue(Gate::forUser($admin)->allows('manage-stocks'));
        $this->assertTrue(Gate::forUser($admin)->allows('manage-users'));

        $this->assertFalse(Gate::forUser($user)->allows('admin'));
        $this->assertFalse(Gate::forUser($user)->allows('manage-stocks'));
        $this->assertFalse(Gate::forUser($user)->allows('manage-users'));

        $this->assertFalse(Gate::forUser($inactiveAdmin)->allows('admin'));
        $this->assertFalse(Gate::forUser($inactiveAdmin)->allows('manage-stocks'));
    }

    public function test_portfolio_gate_authorization(): void
    {
        $activeUser = User::factory()->user()->create();
        $inactiveUser = User::factory()->inactive()->create();

        $this->assertTrue(Gate::forUser($activeUser)->allows('access-portfolio'));
        $this->assertFalse(Gate::forUser($inactiveUser)->allows('access-portfolio'));
    }
}
