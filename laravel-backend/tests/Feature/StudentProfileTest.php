<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Student;
use App\Models\Department;
use Laravel\Sanctum\Sanctum;

class StudentProfileTest extends TestCase
{
    use RefreshDatabase;

    protected $student;
    protected $admin;
    protected $department;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a department manually to avoid factory issues
        $this->department = Department::create([
            'name' => 'Computer Science',
            'code' => 'CS',
            'description' => 'Computer Science Department'
        ]);

        // Create a student user with extended fields
        $studentUser = User::factory()->create([
            'role' => 'student',
            'name' => 'John Doe',
            'email' => 'student@example.com',
            'phone' => '+1234567890',
            'address' => '123 Main St, City, Country',
            'program' => 'Bachelor of Computer Science',
            'year_level' => '3rd year',
            'student_status' => 'active',
            'enrollment_date' => now()->subYears(2),
            'current_cgpa' => 3.5,
            'bio' => 'A passionate computer science student',
            'social_links' => [
                'linkedin' => 'https://linkedin.com/in/johndoe',
                'github' => 'https://github.com/johndoe'
            ]
        ]);

        // Create student profile manually to avoid factory issues
        $this->student = Student::create([
            'user_id' => $studentUser->id,
            'student_id' => 'STU2023001',
            'department_id' => $this->department->id,
            'admission_date' => now()->subYears(2),
            'semester' => 6,
            'section' => 'A',
            'batch' => '2021-2025',
            'current_gpa' => 3.5,
            'total_credits' => 90,
            'status' => 'enrolled'
        ]);

        // Create an admin user
        $this->admin = User::factory()->create([
            'role' => 'admin',
            'email' => 'admin@example.com'
        ]);
    }

    public function test_student_can_view_own_profile()
    {
        Sanctum::actingAs($this->student->user, ['*']);

        $response = $this->getJson('/api/student/profile');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'student' => [
                            'id',
                            'student_id',
                            'user' => [
                                'id',
                                'name',
                                'email',
                                'program',
                                'year_level',
                                'bio',
                                'social_links'
                            ],
                            'department'
                        ],
                        'statistics'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'student' => [
                            'student_id' => 'STU2023001',
                            'user' => [
                                'name' => 'John Doe',
                                'email' => 'student@example.com',
                                'program' => 'Bachelor of Computer Science',
                                'year_level' => '3rd year'
                            ]
                        ]
                    ]
                ]);
    }

    public function test_admin_can_view_any_student_profile()
    {
        Sanctum::actingAs($this->admin, ['*']);

        $response = $this->getJson("/api/students/{$this->student->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'student',
                        'statistics'
                    ]
                ])
                ->assertJson([
                    'success' => true
                ]);
    }

    public function test_student_can_update_own_profile()
    {
        Sanctum::actingAs($this->student->user, ['*']);

        $updateData = [
            'name' => 'John Updated Doe',
            'phone' => '+9876543210',
            'bio' => 'Updated bio for John Doe',
            'program' => 'Bachelor of Software Engineering',
            'year_level' => '4th year',
            'social_links' => [
                'linkedin' => 'https://linkedin.com/in/johnupdated',
                'twitter' => 'https://twitter.com/johnupdated'
            ]
        ];

        $response = $this->putJson('/api/student/profile', $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Student profile updated successfully'
                ]);

        // Verify the user data was updated
        $this->student->user->refresh();
        $this->assertEquals('John Updated Doe', $this->student->user->name);
        $this->assertEquals('+9876543210', $this->student->user->phone);
        $this->assertEquals('Updated bio for John Doe', $this->student->user->bio);
        $this->assertEquals('Bachelor of Software Engineering', $this->student->user->program);
        $this->assertEquals('4th year', $this->student->user->year_level);
        $this->assertEquals('https://linkedin.com/in/johnupdated', $this->student->user->social_links['linkedin']);
        $this->assertEquals('https://twitter.com/johnupdated', $this->student->user->social_links['twitter']);
    }

    public function test_admin_can_update_any_student_profile()
    {
        Sanctum::actingAs($this->admin, ['*']);

        $updateData = [
            'name' => 'Admin Updated Name',
            'semester' => 7,
            'section' => 'B',
            'student_status' => 'graduated'
        ];

        $response = $this->putJson("/api/students/{$this->student->id}", $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Student profile updated successfully'
                ]);

        // Verify updates
        $this->student->refresh();
        $this->student->user->refresh();
        $this->assertEquals('Admin Updated Name', $this->student->user->name);
        $this->assertEquals(7, $this->student->semester);
        $this->assertEquals('B', $this->student->section);
        $this->assertEquals('graduated', $this->student->user->student_status);
    }

    public function test_student_cannot_update_other_student_profile()
    {
        // Create another student
        $otherStudentUser = User::factory()->create(['role' => 'student']);
        $otherStudent = Student::create([
            'user_id' => $otherStudentUser->id,
            'student_id' => 'STU2023002',
            'department_id' => $this->department->id,
            'admission_date' => now()->subYear(),
            'status' => 'enrolled'
        ]);

        Sanctum::actingAs($this->student->user, ['*']);

        $response = $this->putJson("/api/students/{$otherStudent->id}", [
            'name' => 'Unauthorized Update'
        ]);

        $response->assertStatus(403)
                ->assertJson([
                    'success' => false,
                    'message' => 'Access denied. Required permission: students.update',
                    'error_code' => 'PERMISSION_DENIED'
                ]);
    }

    public function test_student_cannot_view_other_student_profile()
    {
        // Create another student
        $otherStudentUser = User::factory()->create(['role' => 'student']);
        $otherStudent = Student::create([
            'user_id' => $otherStudentUser->id,
            'student_id' => 'STU2023003',
            'department_id' => $this->department->id,
            'admission_date' => now()->subYear(),
            'status' => 'enrolled'
        ]);

        Sanctum::actingAs($this->student->user, ['*']);

        $response = $this->getJson("/api/students/{$otherStudent->id}");

        $response->assertStatus(403)
                ->assertJson([
                    'success' => false,
                    'message' => 'Access denied. Required permission: students.read',
                    'error_code' => 'PERMISSION_DENIED'
                ]);
    }

    public function test_profile_update_validation()
    {
        Sanctum::actingAs($this->student->user, ['*']);

        // Test invalid email
        $response = $this->putJson('/api/student/profile', [
            'email' => 'invalid-email'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['email']);

        // Test invalid year level
        $response = $this->putJson('/api/student/profile', [
            'year_level' => 'invalid-year'
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['year_level']);

        // Test invalid CGPA
        $response = $this->putJson('/api/student/profile', [
            'current_cgpa' => 5.0 // Should be max 4.0
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['current_cgpa']);

        // Test invalid social links
        $response = $this->putJson('/api/student/profile', [
            'social_links' => [
                'facebook' => 'not-a-url'
            ]
        ]);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['social_links.facebook']);
    }

    public function test_profile_update_with_student_fields()
    {
        Sanctum::actingAs($this->student->user, ['*']);

        $updateData = [
            'semester' => 8,
            'section' => 'C',
            'batch' => '2021-2026',
            'parent_name' => 'Updated Parent Name',
            'parent_phone' => '+1111111111',
            'emergency_contact' => '+2222222222'
        ];

        $response = $this->putJson('/api/student/profile', $updateData);

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true
                ]);

        // Verify student data was updated
        $this->student->refresh();
        $this->assertEquals(8, $this->student->semester);
        $this->assertEquals('C', $this->student->section);
        $this->assertEquals('2021-2026', $this->student->batch);
        $this->assertEquals('Updated Parent Name', $this->student->parent_name);
        $this->assertEquals('+1111111111', $this->student->parent_phone);
        $this->assertEquals('+2222222222', $this->student->emergency_contact);
    }

    public function test_unauthenticated_user_cannot_access_profile()
    {
        $response = $this->getJson('/api/student/profile');
        $response->assertStatus(401);

        $response = $this->getJson("/api/students/{$this->student->id}");
        $response->assertStatus(401);
    }

    public function test_nonexistent_student_profile_returns_404()
    {
        Sanctum::actingAs($this->admin, ['*']);

        $response = $this->getJson('/api/students/99999');

        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => 'Student not found'
                ]);
    }
}
