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
        ]);

        $response = $this->postJson('/api/auth/password-reset-request', [
            'email' => 'test@example.com'
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => ['token', 'expires_in']
                ]);

        $this->assertDatabaseHas('password_reset_tokens', [
            'email' => 'test@example.com'
        ]);
    }

    public function test_password_reset_request_fails_for_nonexistent_email()
    {
        $response = $this->postJson('/api/auth/password-reset-request', [
            'email' => 'nonexistent@example.com'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);
    }

    public function test_user_can_reset_password_with_valid_token()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('old-password')
        ]);

        // Generate reset token
        $token = PasswordResetToken::createTokenForEmail('test@example.com');

        $response = $this->postJson('/api/auth/password-reset', [
            'email' => 'test@example.com',
            'token' => $token,
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123'
        ]);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Password reset successfully'
                ]);

        // Verify password was changed
        $user->refresh();
        $this->assertTrue(Hash::check('new-password123', $user->password));

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
        ]);

        $response = $this->postJson('/api/auth/password-reset', [
            'email' => 'test@example.com',
            'token' => 'invalid-token',
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123'
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

        $response = $this->postJson('/api/auth/password-reset', [
            'email' => 'test@example.com',
            'token' => 'expired-token',
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123'
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
            'password' => 'new-password123',
            'password_confirmation' => 'different-password'
        ]);
        
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['password']);

        // Test short password
        $response = $this->postJson('/api/auth/password-reset', [
            'email' => $user->email,
            'token' => $token,
            'password' => '123',
            'password_confirmation' => '123'
        ]);
        
        $response->assertStatus(422)
                ->assertJsonValidationErrors(['password']);
    }

    public function test_multiple_reset_requests_replace_previous_tokens()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        // First request
        $response1 = $this->postJson('/api/auth/password-reset-request', [
            'email' => 'test@example.com'
        ]);
        $token1 = $response1->json('data.token');

        // Second request
        $response2 = $this->postJson('/api/auth/password-reset-request', [
            'email' => 'test@example.com'
        ]);
        $token2 = $response2->json('data.token');

        // Only one token should exist (the latest one)
        $this->assertEquals(1, PasswordResetToken::where('email', 'test@example.com')->count());
        
        // First token should be invalid
        $this->assertFalse(PasswordResetToken::isValidToken('test@example.com', $token1));
        
        // Second token should be valid
        $this->assertTrue(PasswordResetToken::isValidToken('test@example.com', $token2));
    }
}
