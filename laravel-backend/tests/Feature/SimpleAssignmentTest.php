<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SimpleAssignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_assignment_crud_operations(): void
    {
        // Create an admin user
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        // Create a course manually (avoid factory issues)
        $course = Course::create([
            'name' => 'Test Course',
            'code' => 'TEST101',
            'description' => 'Test Course Description',
            'credits' => 3,
            'department_id' => 1, // Simple department ID
        ]);

        Sanctum::actingAs($admin, ['*']);

        // Test CREATE assignment
        $assignmentData = [
            'course_id' => $course->id,
            'title' => 'Test Assignment',
            'description' => 'This is a test assignment description.',
            'due_date' => now()->addWeeks(2)->toISOString(),
            'max_score' => 100,
            'status' => 'published',
        ];

        $response = $this->postJson('/api/admin/assignments', $assignmentData);
        $response->assertStatus(201);
        
        $assignmentId = $response->json('data.assignment.id');

        // Test READ single assignment
        $response = $this->getJson("/api/admin/assignments/{$assignmentId}");
        $response->assertStatus(200)
                ->assertJsonPath('data.assignment.title', 'Test Assignment');

        // Test UPDATE assignment
        $updateData = ['title' => 'Updated Assignment Title'];
        $response = $this->putJson("/api/admin/assignments/{$assignmentId}", $updateData);
        $response->assertStatus(200);

        // Verify update
        $response = $this->getJson("/api/admin/assignments/{$assignmentId}");
        $response->assertJsonPath('data.assignment.title', 'Updated Assignment Title');

        // Test GET all assignments
        $response = $this->getJson('/api/admin/assignments');
        $response->assertStatus(200);

        // Test DELETE assignment
        $response = $this->deleteJson("/api/admin/assignments/{$assignmentId}");
        $response->assertStatus(200);

        // Verify deletion
        $response = $this->getJson("/api/admin/assignments/{$assignmentId}");
        $response->assertStatus(404);
    }

    public function test_assignment_validation(): void
    {
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        Sanctum::actingAs($admin, ['*']);

        // Test validation errors
        $response = $this->postJson('/api/admin/assignments', []);
        $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'course_id',
                    'title',
                    'description',
                    'due_date',
                    'max_score'
                ]);
    }
}
