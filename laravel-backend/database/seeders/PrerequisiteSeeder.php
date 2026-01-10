<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CoursePrerequisite;
use App\Models\Course;

class PrerequisiteSeeder extends Seeder
{
    public function run(): void
    {
        $courses = Course::all();
        
        if ($courses->count() < 10) {
            $this->command->warn('Not enough courses to seed prerequisites');
            return;
        }

        // Example: Data Structures requires Programming Fundamentals
        if ($courses->count() >= 2) {
            CoursePrerequisite::create([
                'course_id' => $courses[1]->id,
                'prerequisite_course_id' => $courses[0]->id,
                'minimum_grade' => 2.00,
                'requirement_type' => 'required'
            ]);
        }

        // Example: Algorithms requires Data Structures
        if ($courses->count() >= 3) {
            CoursePrerequisite::create([
                'course_id' => $courses[2]->id,
                'prerequisite_course_id' => $courses[1]->id,
                'minimum_grade' => 2.50,
                'requirement_type' => 'required'
            ]);
        }

        // Example: Advanced course with multiple prerequisites
        if ($courses->count() >= 5) {
            CoursePrerequisite::create([
                'course_id' => $courses[4]->id,
                'prerequisite_course_id' => $courses[2]->id,
                'minimum_grade' => 3.00,
                'requirement_type' => 'required'
            ]);

            CoursePrerequisite::create([
                'course_id' => $courses[4]->id,
                'prerequisite_course_id' => $courses[3]->id,
                'minimum_grade' => null,
                'requirement_type' => 'recommended'
            ]);
        }

        // Example: Corequisite relationship
        if ($courses->count() >= 7) {
            CoursePrerequisite::create([
                'course_id' => $courses[6]->id,
                'prerequisite_course_id' => $courses[5]->id,
                'minimum_grade' => null,
                'requirement_type' => 'corequisite'
            ]);
        }

        // Add some more prerequisites
        $prerequisiteRelations = [
            [7, 4, 2.50, 'required'],
            [8, 5, 2.00, 'required'],
            [9, 6, 3.00, 'required'],
            [9, 7, null, 'recommended'],
        ];

        foreach ($prerequisiteRelations as $relation) {
            if ($courses->count() > $relation[0]) {
                CoursePrerequisite::create([
                    'course_id' => $courses[$relation[0]]->id,
                    'prerequisite_course_id' => $courses[$relation[1]]->id,
                    'minimum_grade' => $relation[2],
                    'requirement_type' => $relation[3]
                ]);
            }
        }

        $this->command->info('Course prerequisites seeded successfully!');
    }
}
