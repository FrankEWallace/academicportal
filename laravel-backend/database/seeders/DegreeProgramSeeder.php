<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DegreeProgram;
use App\Models\ProgramRequirement;
use App\Models\Course;

class DegreeProgramSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Degree Programs
        $bscs = DegreeProgram::create([
            'program_code' => 'BSCS',
            'program_name' => 'Bachelor of Science in Computer Science',
            'department' => 'Computer Science',
            'level' => 'undergraduate',
            'duration_years' => 4,
            'total_credits_required' => 120,
            'minimum_cgpa' => 2.00,
            'description' => 'A comprehensive program covering software development, algorithms, systems programming, and computer theory.',
            'status' => 'active'
        ]);

        $bsit = DegreeProgram::create([
            'program_code' => 'BSIT',
            'program_name' => 'Bachelor of Science in Information Technology',
            'department' => 'Information Technology',
            'level' => 'undergraduate',
            'duration_years' => 4,
            'total_credits_required' => 120,
            'minimum_cgpa' => 2.00,
            'description' => 'Focuses on practical IT skills including networking, database administration, and web development.',
            'status' => 'active'
        ]);

        $bsmath = DegreeProgram::create([
            'program_code' => 'BSMATH',
            'program_name' => 'Bachelor of Science in Mathematics',
            'department' => 'Mathematics',
            'level' => 'undergraduate',
            'duration_years' => 4,
            'total_credits_required' => 120,
            'minimum_cgpa' => 2.00,
            'description' => 'Advanced study of pure and applied mathematics including calculus, algebra, and statistics.',
            'status' => 'active'
        ]);

        $mscs = DegreeProgram::create([
            'program_code' => 'MSCS',
            'program_name' => 'Master of Science in Computer Science',
            'department' => 'Computer Science',
            'level' => 'graduate',
            'duration_years' => 2,
            'total_credits_required' => 30,
            'minimum_cgpa' => 3.00,
            'description' => 'Advanced graduate program in computer science with research focus.',
            'status' => 'active'
        ]);

        // Get some courses to assign as requirements
        $courses = Course::take(20)->get();

        if ($courses->count() > 0) {
            // BSCS Program Requirements
            foreach ($courses->take(10) as $index => $course) {
                ProgramRequirement::create([
                    'degree_program_id' => $bscs->id,
                    'course_id' => $course->id,
                    'requirement_type' => $index < 4 ? 'core' : ($index < 7 ? 'major' : 'elective'),
                    'semester_recommended' => ceil(($index + 1) / 2),
                    'is_mandatory' => $index < 4
                ]);
            }

            // BSIT Program Requirements
            foreach ($courses->skip(5)->take(10) as $index => $course) {
                ProgramRequirement::create([
                    'degree_program_id' => $bsit->id,
                    'course_id' => $course->id,
                    'requirement_type' => $index < 4 ? 'core' : ($index < 7 ? 'major' : 'general_education'),
                    'semester_recommended' => ceil(($index + 1) / 2),
                    'is_mandatory' => $index < 4
                ]);
            }

            // BSMATH Program Requirements
            foreach ($courses->skip(10)->take(8) as $index => $course) {
                ProgramRequirement::create([
                    'degree_program_id' => $bsmath->id,
                    'course_id' => $course->id,
                    'requirement_type' => $index < 3 ? 'core' : ($index < 6 ? 'major' : 'elective'),
                    'semester_recommended' => ceil(($index + 1) / 2),
                    'is_mandatory' => $index < 3
                ]);
            }

            // MSCS Program Requirements
            foreach ($courses->skip(15)->take(6) as $index => $course) {
                ProgramRequirement::create([
                    'degree_program_id' => $mscs->id,
                    'course_id' => $course->id,
                    'requirement_type' => $index < 3 ? 'core' : 'major',
                    'semester_recommended' => $index < 3 ? 1 : 2,
                    'is_mandatory' => $index < 3
                ]);
            }
        }

        $this->command->info('Degree programs and requirements seeded successfully!');
    }
}

