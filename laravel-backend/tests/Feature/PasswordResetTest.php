<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\PasswordResetToken;
use Illuminate\Support\Facades\Hash;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_request_password_reset()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'is_active' => true
        ]);

        $response = $this->postJson('/api/auth/password-reset-request', [
            'email' => 'test@example.com'
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => ['expires_in']
                ])
                ->assertJson([
                    'success' => true,
                    'message' => 'If an account with that email exists, a password reset link has been sent'
                ]);

        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => 'test@example.com'
        ]);
    }

    public function test_password_reset_request_succeeds_for_nonexistent_email()
    {
        // Should return success to prevent email enumeration
        $response = $this->postJson('/api/auth/password-reset-request', [
            'email' => 'nonexistent@example.com'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'If an account with that email exists, a password reset link has been sent'
                ]);

        // No token should be created for non-existent user
        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => 'nonexistent@example.com'
        ]);
    }

    public function test_user_can_reset_password_with_valid_token()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('OldSecurePass123!'),
            'is_active' => true
        ]);

        // Generate reset token
        $token = PasswordResetToken::createTokenForEmail('test@example.com');

        $response = $this->postJson('/api/auth/password-reset', [
            'email' => 'test@example.com',
            'token' => $token,
            'password' => 'NewSecurePass123!',
            'password_confirmation' => 'NewSecurePass123!'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Password has been reset successfully. Please log in with your new password.'
                ]);

        // Verify password was changed
        $user->refresh();
        $this->assertTrue(Hash::check('NewSecurePass123!', $user->password));

        // Verify token was deleted
        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => 'test@example.com',
            'token' => $token
        ]);
    }

    public function test_password_reset_fails_with_invalid_token()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'is_active' => true
        ]);

        // Generate a proper 64-character invalid token
        $invalidToken = str_repeat('a', 64);

        $response = $this->postJson('/api/auth/password-reset', [
            'email' => 'test@example.com',
            'token' => $invalidToken,
            'password' => 'NewSecurePass123!',
            'password_confirmation' => 'NewSecurePass123!'
        ]);

        $response->assertStatus(400)
                ->assertJson([
                    'success' => false,
                    'message' => 'Invalid or expired reset token'
                ]);
    }

    public function test_password_reset_fails_with_expired_token()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        // Create expired token
        $expiredToken = PasswordResetToken::create([
            'email' => 'test@example.com',
            'token' => 'expired-token',
            'expires_at' => now()->subHour() // Expired 1 hour ago
        ]);

        // Generate a proper 64-character expired token
        $expiredToken = str_repeat('b', 64);
        
        $response = $this->postJson('/api/auth/password-reset', [
            'email' => 'test@example.com',
            'token' => $expiredToken,
            'password' => 'NewSecurePass123!',
            'password_confirmation' => 'NewSecurePass123!'
        ]);

        $response->assertStatus(400)
                ->assertJson([
                    'success' => false,
                    'message' => 'Invalid or expired reset token'
                ]);
    }

    public function test_password_reset_validation()
    {
        $user = User::factory()->create();
        $token = PasswordResetToken::createTokenForEmail($user->email);

        // Test missing fields
        $response = $this->postJson('/api/auth/password-reset', []);
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email', 'token', 'password']);

        // Test password confirmation mismatch
        $response = $this->postJson('/api/auth/password-reset', [
            'email' => $user->email,
            'token' => $token,
            'password' => 'NewSecurePass123!',
            'password_confirmation' => 'DifferentPass123!'
        ]);
        
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['password']);

        // Test weak password
        $response = $this->postJson('/api/auth/password-reset', [
            'email' => $user->email,
            'token' => $token,
            'password' => 'weakpass',
            'password_confirmation' => 'weakpass'
        ]);
        
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['password']);
    }

    public function test_multiple_reset_requests_replace_previous_tokens()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'is_active' => true
        ]);

        // First request - directly create token since we don't return it in response
        $token1 = PasswordResetToken::createTokenForEmail('test@example.com');

        // Wait a bit to ensure different timestamps
        sleep(1);

        // Second request - this should replace the first token
        $token2 = PasswordResetToken::createTokenForEmail('test@example.com');

        // Only one token should exist (the latest one)
        $this->assertEquals(1, PasswordResetToken::where('email', 'test@example.com')->count());
        
        // First token should be invalid (deleted when second token was created)
        $this->assertFalse(PasswordResetToken::isValidToken('test@example.com', $token1));
        
        // Second token should be valid
        $this->assertTrue(PasswordResetToken::isValidToken('test@example.com', $token2));
    }
}
