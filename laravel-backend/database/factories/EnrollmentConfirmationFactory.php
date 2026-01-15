<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EnrollmentConfirmation>
 */
class EnrollmentConfirmationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $semesterCodes = ['2024/2025-1', '2024/2025-2', '2025/2026-1', '2025/2026-2'];
        
        return [
            'student_id' => \App\Models\Student::factory(),
            'semester_code' => fake()->randomElement($semesterCodes),
            'confirmation_date' => fake()->dateTimeBetween('-3 months', 'now'),
            'total_units' => fake()->numberBetween(12, 24),
            'timetable_understood' => true,
            'attendance_policy_agreed' => true,
            'academic_calendar_checked' => true,
            'confirmation_email_sent' => fake()->boolean(90),
            'email_sent_at' => fake()->boolean(90) ? fake()->dateTimeBetween('-3 months', 'now') : null,
        ];
    }
}
