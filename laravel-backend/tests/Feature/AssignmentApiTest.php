<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AssignmentApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private User $admin;
    private Course $course;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an admin user
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@test.com'
        ]);

        // Create a simple course manually (avoid factory issues)
        $this->course = new Course();
        $this->course->name = 'Test Course';
        $this->course->code = 'TEST101';
        $this->course->description = 'Test Course Description';
        $this->course->credits = 3;
        $this->course->department_id = 1; // Set a default department_id
        $this->course->save();
    }

    public function test_admin_can_create_assignment(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $assignmentData = [
            'course_id' => $this->course->id,
            'title' => 'Test Assignment',
            'description' => 'This is a test assignment description.',
            'due_date' => now()->addWeeks(2)->toISOString(),
            'max_score' => 100,
            'status' => 'published',
        ];

        $response = $this->postJson('/api/admin/assignments', $assignmentData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'assignment' => [
                            'id',
                            'course_id',
                            'title',
                            'description',
                            'due_date',
                            'max_score',
                            'status',
                            'course'
                        ]
                    ]
                ]);

        $this->assertDatabaseHas('assignments', [
            'title' => 'Test Assignment',
            'course_id' => $this->course->id,
        ]);
    }

    public function test_admin_can_get_all_assignments(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        // Create test assignments manually
        for ($i = 1; $i <= 3; $i++) {
            Assignment::create([
                'course_id' => $this->course->id,
                'title' => "Test Assignment $i",
                'description' => "Description for test assignment $i",
                'due_date' => now()->addWeeks($i),
                'max_score' => 100,
                'status' => 'published',
            ]);
        }

        $response = $this->getJson('/api/admin/assignments');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'data' => [
                            '*' => [
                                'id',
                                'course_id',
                                'title',
                                'description',
                                'due_date',
                                'max_score',
                                'status',
                                'course'
                            ]
                        ]
                    ]
                ]);
    }

    public function test_admin_can_get_single_assignment(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $assignment = Assignment::factory()->create(['course_id' => $this->course->id]);

        $response = $this->getJson("/api/admin/assignments/{$assignment->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'assignment' => [
                            'id',
                            'course_id',
                            'title',
                            'description',
                            'due_date',
                            'max_score',
                            'status'
                        ],
                        'stats'
                    ]
                ]);
    }

    public function test_admin_can_update_assignment(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $assignment = Assignment::factory()->create(['course_id' => $this->course->id]);

        $updateData = [
            'title' => 'Updated Assignment Title',
            'max_score' => 150,
        ];

        $response = $this->putJson("/api/admin/assignments/{$assignment->id}", $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Assignment updated successfully'
                ]);

        $this->assertDatabaseHas('assignments', [
            'id' => $assignment->id,
            'title' => 'Updated Assignment Title',
            'max_score' => 150,
        ]);
    }

    public function test_admin_can_delete_assignment(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        $assignment = Assignment::factory()->create(['course_id' => $this->course->id]);

        $response = $this->deleteJson("/api/admin/assignments/{$assignment->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Assignment deleted successfully'
                ]);

        $this->assertDatabaseMissing('assignments', [
            'id' => $assignment->id,
        ]);
    }

    public function test_validation_errors_on_assignment_creation(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

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

    public function test_can_get_assignments_by_course(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        // Create assignments for the course
        Assignment::factory(2)->published()->create(['course_id' => $this->course->id]);
        
        $response = $this->getJson("/api/courses/{$this->course->id}/assignments");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'course',
                        'assignments'
                    ]
                ]);
    }

    public function test_can_get_upcoming_assignments(): void
    {
        Sanctum::actingAs($this->admin, ['*']);

        // Create upcoming assignments
        Assignment::factory(3)->upcoming()->create(['course_id' => $this->course->id]);

        $response = $this->getJson('/api/assignments/upcoming');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'assignments'
                    ]
                ]);
    }
}
