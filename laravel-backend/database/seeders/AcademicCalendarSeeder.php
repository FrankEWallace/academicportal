<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AcademicCalendar;
use Carbon\Carbon;

class AcademicCalendarSeeder extends Seeder
{
    public function run(): void
    {
        $events = [
            [
                'title' => 'Fall Semester Begins',
                'description' => 'First day of Fall semester classes',
                'event_type' => 'semester_start',
                'start_date' => Carbon::create(2026, 1, 15),
                'end_date' => Carbon::create(2026, 1, 15),
                'semester' => 1,
                'academic_year' => 2026,
                'status' => 'ongoing',
                'is_holiday' => false
            ],
            [
                'title' => 'Course Registration - Spring 2026',
                'description' => 'Registration period for Spring semester courses',
                'event_type' => 'registration_period',
                'start_date' => Carbon::create(2026, 2, 1),
                'end_date' => Carbon::create(2026, 2, 15),
                'semester' => 2,
                'academic_year' => 2026,
                'status' => 'scheduled',
                'is_holiday' => false
            ],
            [
                'title' => 'Mid-Term Examinations',
                'description' => 'Mid-term exams for all courses',
                'event_type' => 'exam_period',
                'start_date' => Carbon::create(2026, 3, 10),
                'end_date' => Carbon::create(2026, 3, 20),
                'semester' => 1,
                'academic_year' => 2026,
                'status' => 'scheduled',
                'is_holiday' => false
            ],
            [
                'title' => 'Spring Break',
                'description' => 'University closed for spring break',
                'event_type' => 'break',
                'start_date' => Carbon::create(2026, 4, 5),
                'end_date' => Carbon::create(2026, 4, 12),
                'semester' => null,
                'academic_year' => 2026,
                'status' => 'scheduled',
                'is_holiday' => true
            ],
            [
                'title' => 'Final Examinations - Fall Semester',
                'description' => 'Final exams for Fall semester',
                'event_type' => 'exam_period',
                'start_date' => Carbon::create(2026, 5, 15),
                'end_date' => Carbon::create(2026, 5, 30),
                'semester' => 1,
                'academic_year' => 2026,
                'status' => 'scheduled',
                'is_holiday' => false
            ],
            [
                'title' => 'Independence Day',
                'description' => 'National holiday - University closed',
                'event_type' => 'holiday',
                'start_date' => Carbon::create(2026, 7, 4),
                'end_date' => Carbon::create(2026, 7, 4),
                'semester' => null,
                'academic_year' => 2026,
                'status' => 'scheduled',
                'is_holiday' => true
            ],
            [
                'title' => 'Assignment Submission Deadline',
                'description' => 'Final deadline for all pending assignments',
                'event_type' => 'other',
                'start_date' => Carbon::create(2026, 5, 10),
                'end_date' => Carbon::create(2026, 5, 10),
                'semester' => 1,
                'academic_year' => 2026,
                'status' => 'scheduled',
                'is_holiday' => false
            ],
            [
                'title' => 'New Student Orientation',
                'description' => 'Orientation program for incoming students',
                'event_type' => 'orientation',
                'start_date' => Carbon::create(2026, 8, 25),
                'end_date' => Carbon::create(2026, 8, 27),
                'semester' => 1,
                'academic_year' => 2026,
                'status' => 'scheduled',
                'is_holiday' => false
            ],
        ];

        foreach ($events as $event) {
            AcademicCalendar::create($event);
        }

        $this->command->info('Academic calendar events seeded successfully!');
    }
}
