<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Department;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Course;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Admin User
        $admin = User::create([
            'name' => 'System Administrator',
            'email' => 'admin@academic-nexus.com',
            'password' => Hash::make('admin123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // Create Departments
        $csDept = Department::create([
            'name' => 'Computer Science',
            'code' => 'CS',
            'description' => 'Department of Computer Science and Engineering',
            'established_year' => 2010,
            'status' => 'active',
        ]);

        $businessDept = Department::create([
            'name' => 'Business Administration',
            'code' => 'BA',
            'description' => 'Department of Business Administration',
            'established_year' => 2008,
            'status' => 'active',
        ]);

        $engineeringDept = Department::create([
            'name' => 'Engineering',
            'code' => 'ENG',
            'description' => 'Department of Engineering',
            'established_year' => 2005,
            'status' => 'active',
        ]);

        // Create Teacher Users
        $teacher1User = User::create([
            'name' => 'Dr. John Smith',
            'email' => 'john.smith@academic-nexus.com',
            'password' => Hash::make('teacher123'),
            'role' => 'teacher',
            'teacher_id' => 'TCH001',
            'is_active' => true,
        ]);

        $teacher1 = Teacher::create([
            'user_id' => $teacher1User->id,
            'employee_id' => 'TCH001',
            'department_id' => $csDept->id,
            'designation' => 'Professor',
            'qualification' => 'PhD in Computer Science',
            'specialization' => 'Database Systems',
            'joining_date' => '2015-01-15',
            'salary' => 75000,
            'experience_years' => 10,
            'status' => 'active',
        ]);

        $teacher2User = User::create([
            'name' => 'Dr. Sarah Johnson',
            'email' => 'sarah.johnson@academic-nexus.com',
            'password' => Hash::make('teacher123'),
            'role' => 'teacher',
            'teacher_id' => 'TCH002',
            'is_active' => true,
        ]);

        $teacher2 = Teacher::create([
            'user_id' => $teacher2User->id,
            'employee_id' => 'TCH002',
            'department_id' => $businessDept->id,
            'designation' => 'Associate Professor',
            'qualification' => 'MBA, PhD in Business',
            'specialization' => 'Marketing Management',
            'joining_date' => '2017-08-20',
            'salary' => 65000,
            'experience_years' => 8,
            'status' => 'active',
        ]);

        // Create Student Users
        $student1User = User::create([
            'name' => 'John Doe',
            'email' => 'john.doe@student.academic-nexus.com',
            'password' => Hash::make('student123'),
            'role' => 'student',
            'student_id' => 'STU001',
            'phone' => '+1234567890',
            'date_of_birth' => '2002-05-15',
            'gender' => 'male',
            'is_active' => true,
        ]);

        $student1 = Student::create([
            'user_id' => $student1User->id,
            'student_id' => 'STU001',
            'department_id' => $csDept->id,
            'admission_date' => '2022-09-01',
            'semester' => 3,
            'section' => 'A',
            'batch' => '2022-26',
            'parent_name' => 'Robert Doe',
            'parent_phone' => '+1234567891',
            'parent_email' => 'robert.doe@email.com',
            'current_gpa' => 3.85,
            'total_credits' => 45,
            'status' => 'enrolled',
        ]);

        $student2User = User::create([
            'name' => 'Jane Smith',
            'email' => 'jane.smith@student.academic-nexus.com',
            'password' => Hash::make('student123'),
            'role' => 'student',
            'student_id' => 'STU002',
            'phone' => '+1234567892',
            'date_of_birth' => '2003-03-22',
            'gender' => 'female',
            'is_active' => true,
        ]);

        $student2 = Student::create([
            'user_id' => $student2User->id,
            'student_id' => 'STU002',
            'department_id' => $businessDept->id,
            'admission_date' => '2022-09-01',
            'semester' => 3,
            'section' => 'B',
            'batch' => '2022-26',
            'parent_name' => 'Michael Smith',
            'parent_phone' => '+1234567893',
            'parent_email' => 'michael.smith@email.com',
            'current_gpa' => 3.92,
            'total_credits' => 48,
            'status' => 'enrolled',
        ]);

        // Create Courses
        Course::create([
            'name' => 'Data Structures and Algorithms',
            'code' => 'CS301',
            'description' => 'Fundamental data structures and algorithms',
            'credits' => 3,
            'department_id' => $csDept->id,
            'teacher_id' => $teacher1->id,
            'semester' => 3,
            'section' => 'A',
            'schedule' => [
                ['day' => 'Monday', 'time' => '09:00-10:30'],
                ['day' => 'Wednesday', 'time' => '09:00-10:30'],
                ['day' => 'Friday', 'time' => '09:00-10:30']
            ],
            'room' => 'Room 301',
            'max_students' => 50,
            'enrolled_students' => 1,
            'start_date' => '2024-01-15',
            'end_date' => '2024-05-15',
            'status' => 'active',
        ]);

        Course::create([
            'name' => 'Marketing Management',
            'code' => 'BA201',
            'description' => 'Principles and practices of marketing management',
            'credits' => 3,
            'department_id' => $businessDept->id,
            'teacher_id' => $teacher2->id,
            'semester' => 2,
            'section' => 'B',
            'schedule' => [
                ['day' => 'Tuesday', 'time' => '11:00-12:30'],
                ['day' => 'Thursday', 'time' => '11:00-12:30']
            ],
            'room' => 'Room 205',
            'max_students' => 45,
            'enrolled_students' => 1,
            'start_date' => '2024-01-15',
            'end_date' => '2024-05-15',
            'status' => 'active',
        ]);

        // Call additional seeders
        $this->call([
            FeeStructureSeeder::class,
            InvoiceSeeder::class,
            PaymentSeeder::class,
        ]);

        echo "Database seeded successfully!\n";
        echo "Admin Login: admin@academic-nexus.com / admin123\n";
        echo "Teacher Login: john.smith@academic-nexus.com / teacher123\n";
        echo "Student Login: john.doe@student.academic-nexus.com / student123\n";
    }
}
