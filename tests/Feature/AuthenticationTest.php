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
            ->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);

        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_login_page_does_not_expose_password_hash(): void
    {
        $user = User::factory()->create(['password' => 'Secret123!']);
        $this->get('/login')->assertOk()->assertDontSee($user->password, false);
    }
}
