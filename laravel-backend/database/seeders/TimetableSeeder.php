<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Timetable;
use App\Models\Course;
use App\Models\Teacher;

class TimetableSeeder extends Seeder
{
    public function run(): void
    {
        $courses = Course::with('teacher')->take(10)->get();
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $rooms = ['101', '102', '201', '202', '301', 'Lab-1', 'Lab-2'];
        $buildings = ['A', 'A', 'B', 'B', 'C', 'Lab', 'Lab'];
        $times = [
            ['08:00:00', '09:30:00'],
            ['10:00:00', '11:30:00'],
            ['13:00:00', '14:30:00'],
            ['15:00:00', '16:30:00'],
        ];

        foreach ($courses as $course) {
            if (!$course->teacher_id) continue;

            // Create 2 sessions per week for each course
            for ($i = 0; $i < 2; $i++) {
                $dayIndex = ($course->id + $i) % count($days);
                $timeIndex = ($course->id) % count($times);
                $roomIndex = $course->id % count($rooms);

                Timetable::create([
                    'course_id' => $course->id,
                    'teacher_id' => $course->teacher_id,
                    'day_of_week' => $days[$dayIndex],
                    'start_time' => $times[$timeIndex][0],
                    'end_time' => $times[$timeIndex][1],
                    'room_number' => $rooms[$roomIndex],
                    'building' => $buildings[$roomIndex],
                    'capacity' => $course->max_students ?? 40,
                    'semester' => $course->semester ?? 1,
                    'academic_year' => date('Y'),
                    'status' => 'active',
                    'notes' => null
                ]);
            }
        }

        $this->command->info('Timetables seeded successfully!');
    }
}
