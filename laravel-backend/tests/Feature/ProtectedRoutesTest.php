<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class ProtectedRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_user_cannot_access_protected_routes()
    {
        // Test admin routes
        $this->getJson('/api/admin/dashboard')
            ->assertStatus(401);

        $this->getJson('/api/admin/users')
            ->assertStatus(401);

        // Test student routes
        $this->getJson('/api/student/courses')
            ->assertStatus(401);

        // Test teacher routes
        $this->getJson('/api/teacher/courses')
            ->assertStatus(401);
    }

    public function test_student_cannot_access_admin_routes()
    {
        $student = User::factory()->create(['role' => 'student']);
        Sanctum::actingAs($student, ['*']);

        $this->getJson('/api/admin/dashboard')
            ->assertStatus(403);

        $this->getJson('/api/admin/users')
            ->assertStatus(403);

        $this->postJson('/api/admin/users', [])
            ->assertStatus(403);
    }

    public function test_teacher_cannot_access_admin_routes()
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        Sanctum::actingAs($teacher, ['*']);

        $this->getJson('/api/admin/dashboard')
            ->assertStatus(403);

        $this->getJson('/api/admin/users')
            ->assertStatus(403);
    }

    public function test_admin_can_access_admin_routes()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin, ['*']);

        $this->getJson('/api/admin/dashboard')
            ->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_students',
                    'active_courses',
                    'faculty_count',
                    'departments_count'
                ]
            ]);
    }

    public function test_student_can_access_student_routes()
    {
        $student = User::factory()->create(['role' => 'student']);
        Sanctum::actingAs($student, ['*']);

        $this->getJson('/api/student/courses')
            ->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data'
            ]);
    }

    public function test_teacher_can_access_teacher_routes()
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        Sanctum::actingAs($teacher, ['*']);

        $this->getJson('/api/teacher/courses')
            ->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data'
            ]);
    }

    public function test_student_cannot_access_other_student_data()
    {
        $student1 = User::factory()->create(['role' => 'student']);
        $student2 = User::factory()->create(['role' => 'student']);
        
        Sanctum::actingAs($student1, ['*']);

        // Student should not be able to access another student's details
        $this->getJson("/api/students/{$student2->id}")
            ->assertStatus(403);
    }

    public function test_role_based_permissions_work_correctly()
    {
        // Admin permissions
        $admin = User::factory()->create(['role' => 'admin']);
        Sanctum::actingAs($admin, ['*']);

        $this->getJson('/api/admin/users')
            ->assertStatus(200);

        // Student permissions - should not access admin routes
        $student = User::factory()->create(['role' => 'student']);
        Sanctum::actingAs($student, ['*']);

        $this->getJson('/api/admin/users')
            ->assertStatus(403);

        $this->getJson('/api/student/courses')
            ->assertStatus(200);
    }

    public function test_mixed_role_routes_work_correctly()
    {
        $student = User::factory()->create(['role' => 'student']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $admin = User::factory()->create(['role' => 'admin']);

        // Test route that allows multiple roles (if you have any)
        // For example, course details might be accessible by all roles
        
        Sanctum::actingAs($student, ['*']);
        // Assuming course details are accessible by students
        // $this->getJson('/api/courses/1')->assertStatus(200);

        Sanctum::actingAs($teacher, ['*']);
        // $this->getJson('/api/courses/1')->assertStatus(200);

        Sanctum::actingAs($admin, ['*']);
        // $this->getJson('/api/courses/1')->assertStatus(200);
    }
}
