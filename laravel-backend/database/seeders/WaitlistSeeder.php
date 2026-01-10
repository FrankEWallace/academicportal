<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CourseWaitlist;
use App\Models\Course;
use App\Models\Student;
use Carbon\Carbon;

class WaitlistSeeder extends Seeder
{
    public function run(): void
    {
        $courses = Course::take(5)->get();
        $students = Student::take(10)->get();

        if ($courses->isEmpty() || $students->isEmpty()) {
            $this->command->warn('Not enough data to seed waitlists');
            return;
        }

        $position = 1;
        
        // Add students to waitlist for first course
        foreach ($students->take(5) as $student) {
            CourseWaitlist::create([
                'course_id' => $courses[0]->id,
                'student_id' => $student->id,
                'position' => $position++,
                'semester' => 1,
                'academic_year' => '2025-2026',
                'status' => 'waiting',
                'added_at' => Carbon::now()->subDays(rand(1, 30)),
                'enrolled_at' => null,
                'removed_at' => null,
                'notes' => null
            ]);
        }

        // Add students to waitlist for second course
        $position = 1;
        foreach ($students->skip(2)->take(3) as $student) {
            CourseWaitlist::create([
                'course_id' => $courses[1]->id,
                'student_id' => $student->id,
                'position' => $position++,
                'semester' => 1,
                'academic_year' => '2025-2026',
                'status' => 'waiting',
                'added_at' => Carbon::now()->subDays(rand(1, 20)),
                'enrolled_at' => null,
                'removed_at' => null,
                'notes' => null
            ]);
        }

        // Add an enrolled waitlist entry (was on waitlist, now enrolled)
        if ($courses->count() > 2 && $students->count() > 5) {
            CourseWaitlist::create([
                'course_id' => $courses[2]->id,
                'student_id' => $students[5]->id,
                'position' => 1,
                'semester' => 1,
                'academic_year' => '2025-2026',
                'status' => 'enrolled',
                'added_at' => Carbon::now()->subDays(45),
                'enrolled_at' => Carbon::now()->subDays(10),
                'removed_at' => null,
                'notes' => 'Auto-enrolled from waitlist'
            ]);
        }

        $this->command->info('Course waitlists seeded successfully!');
    }
}
