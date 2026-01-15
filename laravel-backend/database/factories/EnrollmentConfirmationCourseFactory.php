<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EnrollmentConfirmationCourse>
 */
class EnrollmentConfirmationCourseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'enrollment_confirmation_id' => \App\Models\EnrollmentConfirmation::factory(),
            'course_id' => \App\Models\Course::factory(),
            'prerequisites_met' => true,
            'no_schedule_conflict' => true,
        ];
    }
}
