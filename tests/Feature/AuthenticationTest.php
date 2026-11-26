<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_management_pages_and_actions_require_authentication(): void
    {
        $this->get('/products')->assertRedirect('/login');
        $this->post('/products', ['name' => 'Hidden write'])->assertRedirect('/login');
        $this->assertDatabaseMissing('products', ['name' => 'Hidden write']);
    }

    public function test_staff_can_log_in_with_username_and_log_out(): void
    {
        $user = User::factory()->create(['username' => 'alex', 'password' => 'Secret123!']);

        $this->post('/login', ['username' => 'alex', 'password' => 'wrong'])
            ->assertSessionHasErrors('username');
        $this->assertGuest();

        $this->post('/login', ['username' => 'alex', 'password' => 'Secret123!'])
            ->assertRedirect('/sales/create')
            ->assertSessionHas('security.guard_started', true)
            ->assertSessionHas('security.last_activity_at')
            ->assertSessionHas('security.last_heartbeat_at');
        $this->assertAuthenticatedAs($user);

        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_login_page_does_not_expose_password_hash(): void
    {
        $user = User::factory()->create(['password' => 'Secret123!']);
        $this->get('/login')->assertOk()->assertDontSee($user->password, false);
    }

    public function test_administrator_and_cashier_land_on_role_appropriate_workspaces(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'password' => 'Secret123!']);

        $this->post('/login', ['username' => $admin->username, 'password' => 'Secret123!'])
            ->assertRedirect('/dashboard');

        $this->post('/logout');
        $cashier = User::factory()->create(['role' => 'cashier', 'password' => 'Secret123!']);
        $this->post('/login', ['username' => $cashier->username, 'password' => 'Secret123!'])
            ->assertRedirect('/sales/create');
    }

    public function test_idle_authenticated_sessions_are_ended_by_the_server(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->withSession([
                'security.guard_started' => true,
                'security.last_activity_at' => now()->subMinutes(16)->timestamp,
                'security.last_heartbeat_at' => now()->timestamp,
            ])
            ->get('/dashboard')
            ->assertRedirect('/login')
            ->assertSessionHas('warning');

        $this->assertGuest();
    }

    public function test_a_missing_browser_heartbeat_ends_the_session(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);

        $this->actingAs($cashier)
            ->withSession([
                'security.guard_started' => true,
                'security.last_activity_at' => now()->timestamp,
                'security.last_heartbeat_at' => now()->subMinutes(4)->timestamp,
            ])
            ->getJson('/products')
            ->assertUnauthorized()
            ->assertJsonPath('message', 'Your secure session has expired.');

        $this->assertGuest();
    }

    public function test_browser_heartbeat_and_activity_refresh_the_secure_session(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);
        $startingTime = now()->subMinute()->timestamp;

        $this->actingAs($cashier)
            ->withSession([
                'security.guard_started' => true,
                'security.last_activity_at' => $startingTime,
                'security.last_heartbeat_at' => $startingTime,
            ])
            ->post('/session/heartbeat')
            ->assertNoContent()
            ->assertSessionHas('security.last_heartbeat_at', fn ($value) => $value > $startingTime);

        $this->post('/session/activity')
            ->assertNoContent()
            ->assertSessionHas('security.last_activity_at', fn ($value) => $value > $startingTime);
    }

    public function test_tab_close_endpoint_logs_out_and_invalidates_the_session(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post('/session/close')
            ->assertNoContent();

        $this->assertGuest();
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_authenticated_layout_contains_the_browser_session_guard(): void
    {
        $cashier = User::factory()->create(['role' => 'cashier']);

        $this->actingAs($cashier)
            ->get('/sales/create')
            ->assertOk()
            ->assertSee('data-session-guard', false)
            ->assertSee(route('session.close'), false)
            ->assertSee('session-restore-lock', false);
    }
}
