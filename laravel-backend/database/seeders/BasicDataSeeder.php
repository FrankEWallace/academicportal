<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Student;
use App\Models\Course;
use App\Models\Department;
use App\Models\Enrollment;
use Illuminate\Support\Facades\Hash;

class BasicDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding basic data...');
        
        // Create Admin User
        $admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@academicnexus.edu',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
        
        $this->command->info('✓ Created admin user');
        
        // Create Department
        $department = Department::create([
            'name' => 'Computer Science',
            'code' => 'CSC',
            'description' => 'Department of Computer Science and Information Technology',
            'email' => 'csc@academicnexus.edu',
            'phone' => '+2348012345678',
            'status' => 'active',
        ]);
        
        $this->command->info('✓ Created department');
        
        // Create 5 Courses
        $courses = [];
        $courseData = [
            ['code' => 'CSC101', 'name' => 'Introduction to Programming', 'credits' => 3],
            ['code' => 'CSC102', 'name' => 'Data Structures', 'credits' => 3],
            ['code' => 'CSC201', 'name' => 'Database Systems', 'credits' => 4],
            ['code' => 'CSC202', 'name' => 'Web Development', 'credits' => 3],
            ['code' => 'CSC301', 'name' => 'Software Engineering', 'credits' => 4],
        ];
        
        foreach ($courseData as $data) {
            $courses[] = Course::create([
                'code' => $data['code'],
                'name' => $data['name'],
                'credits' => $data['credits'],
                'department_id' => $department->id,
                'semester' => 1, // 1 = Fall semester
                'max_capacity' => 50,
                'enrolled_students' => 0,
                'start_date' => now()->subMonths(3)->format('Y-m-d'),
                'end_date' => now()->addMonths(2)->format('Y-m-d'),
                'status' => 'active',
            ]);
        }
        
        $this->command->info('✓ Created 5 courses');
        
        // Create 10 Students with enrollments
        for ($i = 1; $i <= 10; $i++) {
            $user = User::create([
                'name' => "Student {$i}",
                'email' => "student{$i}@academicnexus.edu",
                'password' => Hash::make('password'),
                'role' => 'student',
                'email_verified_at' => now(),
            ]);
            
            $student = Student::create([
                'user_id' => $user->id,
                'student_id' => 'STU' . str_pad($i, 5, '0', STR_PAD_LEFT),
                'department_id' => $department->id,
                'admission_date' => now()->subYears(rand(1, 3))->format('Y-m-d'),
                'current_semester' => rand(1, 8),
            ]);
            
            // Enroll in 3-5 random courses
            $enrollmentCount = rand(3, 5);
            $selectedCourses = collect($courses)->random($enrollmentCount);
            
            foreach ($selectedCourses as $course) {
                Enrollment::create([
                    'student_id' => $student->id,
                    'course_id' => $course->id,
                    'enrollment_date' => now()->subMonths(3)->format('Y-m-d'),
                    'status' => 'enrolled',
                ]);
                
                // Update enrolled count
                $course->increment('enrolled_students');
            }
        }
        
        $this->command->info('✓ Created 10 students with course enrollments');
        $this->command->info('✅ Basic data seeding completed!');
        $this->command->info('');
        $this->command->info('Login credentials:');
        $this->command->info('  Admin: admin@academicnexus.edu / password');
        $this->command->info('  Students: student1@academicnexus.edu ... student10@academicnexus.edu / password');
    }
}

