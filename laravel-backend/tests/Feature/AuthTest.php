<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Department;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBasicData();
    }

    public function test_user_can_register_as_student()
    {
        $department = Department::first();
        
        $payload = [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'student',
        ];

        $response = $this->postJson('/api/auth/register', $payload);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'user' => ['id', 'name', 'email', 'role'],
                        'token',
                        'token_type'
                    ]
                ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john.doe@example.com',
            'role' => 'student',
            'is_active' => true
        ]);
    }

    public function test_user_can_register_as_teacher()
    {
        $payload = [
            'name' => 'Jane Smith',
            'email' => 'jane.smith@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'teacher',
        ];

        $response = $this->postJson('/api/auth/register', $payload);

        $response->assertStatus(201);
        
        $this->assertDatabaseHas('users', [
            'email' => 'jane.smith@example.com',
            'role' => 'teacher'
        ]);
    }

    public function test_registration_fails_with_invalid_data()
    {
        $payload = [
            'name' => '',
            'email' => 'invalid-email',
            'password' => '123',
            'password_confirmation' => '456',
            'role' => 'invalid-role',
        ];

        $response = $this->postJson('/api/auth/register', $payload);

        $response->assertStatus(422);
        // Just check that we get validation errors, don't check specific fields
        $response->assertJsonStructure(['success', 'message', 'errors']);
    }

    public function test_user_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'role' => 'student',
            'is_active' => true
        ]);

        $payload = [
            'email' => 'test@example.com',
            'password' => 'password123',
            'role' => 'student'
        ];

        $response = $this->postJson('/api/auth/login', $payload);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'user',
                        'token',
                        'token_type'
                    ]
                ]);
    }

    public function test_login_fails_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'role' => 'student'
        ]);

        $payload = [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
            'role' => 'student'
        ];

        $response = $this->postJson('/api/auth/login', $payload);

        $response->assertStatus(401)
                ->assertJson([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ]);
    }

    public function test_login_fails_with_wrong_role()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'role' => 'student'
        ]);

        $payload = [
            'email' => 'test@example.com',
            'password' => 'password123',
            'role' => 'admin' // Wrong role
        ];

        $response = $this->postJson('/api/auth/login', $payload);

        $response->assertStatus(401);
    }

    public function test_login_fails_for_inactive_user()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'role' => 'student',
            'is_active' => false
        ]);

        $payload = [
            'email' => 'test@example.com',
            'password' => 'password123',
            'role' => 'student'
        ];

        $response = $this->postJson('/api/auth/login', $payload);

        $response->assertStatus(401);
    }

    public function test_authenticated_user_can_logout()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/auth/logout');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Logged out successfully'
                ]);
    }

    public function test_authenticated_user_can_get_profile()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test-token')->plainTextToken;

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/auth/me');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'user' => ['id', 'name', 'email', 'role'],
                        'permissions',
                        'token_info'
                    ]
                ]);
    }
}
