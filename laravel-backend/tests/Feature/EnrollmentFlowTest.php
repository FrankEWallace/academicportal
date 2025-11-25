<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Course;
use App\Models\Department;
use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Teacher;
use Laravel\Sanctum\Sanctum;

class EnrollmentFlowTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $teacher;
    protected $student;
    protected $course;
    protected $department;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->teacher = User::factory()->create(['role' => 'teacher']);
        $this->student = User::factory()->create(['role' => 'student']);

        // Get or create department
        $this->department = Department::first();

        // Create teacher profile
        Teacher::factory()->create([
            'user_id' => $this->teacher->id,
            'employee_id' => 'EMP' . str_pad($this->teacher->id, 6, '0', STR_PAD_LEFT),
            'department_id' => $this->department->id,
        ]);

        // Create student profile
        Student::factory()->create([
            'user_id' => $this->student->id,
            'student_id' => 'STU' . str_pad($this->student->id, 6, '0', STR_PAD_LEFT),
            'department_id' => $this->department->id,
        ]);

        // Create a test course
        $this->course = Course::factory()->create([
            'department_id' => $this->department->id,
            'teacher_id' => $this->teacher->teacher->id,
        ]);
    }

    public function test_complete_enrollment_flow_by_admin()
    {
        Sanctum::actingAs($this->admin, ['*']);

        // Step 1: Admin views available courses
        $coursesResponse = $this->getJson('/api/admin/courses');
        $coursesResponse->assertStatus(200)
                       ->assertJsonStructure([
                           'success',
                           'data' => [
                               'data' => [
                                   '*' => ['id', 'name', 'code']
                               ]
                           ]
                       ]);

        // Step 2: Admin views students
        $studentsResponse = $this->getJson('/api/admin/users?role=student');
        $studentsResponse->assertStatus(200);

        // Step 3: Admin creates enrollment
        $enrollmentData = [
            'student_id' => $this->student->student->id,
            'course_id' => $this->course->id,
        ];

        $enrollmentResponse = $this->postJson('/api/enrollments', $enrollmentData);
        $enrollmentResponse->assertStatus(201)
                          ->assertJsonStructure([
                              'success',
                              'message',
                              'data' => ['id', 'student_id', 'course_id', 'status']
                          ]);

        // Step 4: Verify enrollment was created
        $this->assertDatabaseHas('enrollments', [
            'student_id' => $this->student->student->id,
            'course_id' => $this->course->id,
            'status' => 'enrolled'
        ]);

        // Step 5: Student can now see the course in their enrolled courses
        Sanctum::actingAs($this->student, ['*']);
        
        $studentCoursesResponse = $this->getJson('/api/student/courses');
        $studentCoursesResponse->assertStatus(200);
        
        $enrolledCourses = $studentCoursesResponse->json('data');
        $this->assertNotEmpty($enrolledCourses);
    }

    public function test_student_enrollment_validation()
    {
        Sanctum::actingAs($this->admin, ['*']);

        // Test enrollment with invalid student ID
        $invalidEnrollmentData = [
            'student_id' => 999999,
            'course_id' => $this->course->id,
        ];

        $response = $this->postJson('/api/enrollments', $invalidEnrollmentData);
        $response->assertStatus(422);

        // Test enrollment with invalid course ID
        $invalidEnrollmentData = [
            'student_id' => $this->student->student->id,
            'course_id' => 999999,
        ];

        $response = $this->postJson('/api/enrollments', $invalidEnrollmentData);
        $response->assertStatus(422);
    }

    public function test_duplicate_enrollment_prevention()
    {
        Sanctum::actingAs($this->admin, ['*']);

        $enrollmentData = [
            'student_id' => $this->student->student->id,
            'course_id' => $this->course->id,
        ];

        // First enrollment should succeed
        $firstEnrollment = $this->postJson('/api/enrollments', $enrollmentData);
        $firstEnrollment->assertStatus(201);

        // Second enrollment should fail
        $secondEnrollment = $this->postJson('/api/enrollments', $enrollmentData);
        $secondEnrollment->assertStatus(409); // Conflict
    }

    public function test_course_capacity_limits()
    {
        // Update course to have limited capacity
        $this->course->update(['max_students' => 1]);

        Sanctum::actingAs($this->admin, ['*']);

        // Create another student
        $anotherStudent = User::factory()->create(['role' => 'student']);
        Student::factory()->create([
            'user_id' => $anotherStudent->id,
            'student_id' => 'STU' . str_pad($anotherStudent->id, 6, '0', STR_PAD_LEFT),
            'department_id' => $this->department->id,
        ]);

        // First enrollment should succeed
        $enrollmentData1 = [
            'student_id' => $this->student->student->id,
            'course_id' => $this->course->id,
        ];
        
        $response1 = $this->postJson('/api/enrollments', $enrollmentData1);
        $response1->assertStatus(201);

        // Second enrollment should fail due to capacity
        $enrollmentData2 = [
            'student_id' => $anotherStudent->student->id,
            'course_id' => $this->course->id,
        ];
        
        $response2 = $this->postJson('/api/enrollments', $enrollmentData2);
        $response2->assertStatus(400); // Bad request - course full
    }

    public function test_student_cannot_create_enrollment()
    {
        Sanctum::actingAs($this->student, ['*']);

        $enrollmentData = [
            'student_id' => $this->student->student->id,
            'course_id' => $this->course->id,
        ];

        $response = $this->postJson('/api/enrollments', $enrollmentData);
        $response->assertStatus(403); // Forbidden
    }

    public function test_teacher_can_view_enrolled_students()
    {
        // Create enrollment first
        Enrollment::factory()->create([
            'student_id' => $this->student->student->id,
            'course_id' => $this->course->id,
            'status' => 'enrolled'
        ]);

        Sanctum::actingAs($this->teacher, ['*']);

        $response = $this->getJson("/api/teacher/courses/{$this->course->id}/students");
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        '*' => ['id', 'student_id', 'user']
                    ]
                ]);
    }

    public function test_enrollment_status_updates()
    {
        Sanctum::actingAs($this->admin, ['*']);

        // Create enrollment
        $enrollmentData = [
            'student_id' => $this->student->student->id,
            'course_id' => $this->course->id,
        ];
        
        $enrollmentResponse = $this->postJson('/api/enrollments', $enrollmentData);
        $enrollmentId = $enrollmentResponse->json('data.id');

        // Update enrollment status
        $updateData = ['status' => 'completed'];
        
        $updateResponse = $this->putJson("/api/enrollments/{$enrollmentId}", $updateData);
        $updateResponse->assertStatus(200);

        // Verify status was updated
        $this->assertDatabaseHas('enrollments', [
            'id' => $enrollmentId,
            'status' => 'completed'
        ]);
    }

    public function test_enrollment_deletion()
    {
        Sanctum::actingAs($this->admin, ['*']);

        // Create enrollment
        $enrollment = Enrollment::factory()->create([
            'student_id' => $this->student->student->id,
            'course_id' => $this->course->id,
        ]);

        // Delete enrollment
        $deleteResponse = $this->deleteJson("/api/enrollments/{$enrollment->id}");
        $deleteResponse->assertStatus(200);

        // Verify enrollment was deleted
        $this->assertDatabaseMissing('enrollments', [
            'id' => $enrollment->id
        ]);
    }
}
