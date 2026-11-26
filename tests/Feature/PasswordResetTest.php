<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetStaffPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_can_request_and_complete_a_password_reset(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'staff@example.test', 'password' => 'Original123!']);

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertSessionHas('success');

        $token = null;
        Notification::assertSentTo($user, ResetStaffPassword::class, function (ResetStaffPassword $notification) use (&$token, $user) {
            $token = $notification->token;
            $mail = $notification->toMail($user);
            $this->assertSame('Reset your Counterly password', $mail->subject);
            $this->assertStringContainsString('/reset-password/'.$token, $mail->actionUrl);

            return true;
        });

        $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]))->assertOk();
        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'Replacement456!',
            'password_confirmation' => 'Replacement456!',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('Replacement456!', $user->fresh()->password));
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
    }

    public function test_reset_request_does_not_reveal_unknown_email_addresses(): void
    {
        Notification::fake();

        $this->post(route('password.email'), ['email' => 'unknown@example.test'])
            ->assertSessionHas('success')
            ->assertSessionHasNoErrors();

        Notification::assertNothingSent();
    }

    public function test_reset_rejects_weak_passwords_and_invalid_tokens(): void
    {
        $user = User::factory()->create(['email' => 'staff@example.test']);

        $this->post(route('password.update'), [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'weak',
            'password_confirmation' => 'weak',
        ])->assertSessionHasErrors('password');

        $this->post(route('password.update'), [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'StrongEnough123!',
            'password_confirmation' => 'StrongEnough123!',
        ])->assertSessionHasErrors('email');
    }
}
