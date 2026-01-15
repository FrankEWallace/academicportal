<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Registration>
 */
class RegistrationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $semesterCodes = ['2024/2025-1', '2024/2025-2', '2025/2026-1', '2025/2026-2'];
        $statuses = ['pending', 'verified', 'rejected'];
        
        return [
            'student_id' => \App\Models\Student::factory(),
            'semester_code' => fake()->randomElement($semesterCodes),
            'registration_date' => fake()->dateTimeBetween('-6 months', 'now'),
            'status' => fake()->randomElement($statuses),
            'verified_by' => fake()->boolean(70) ? \App\Models\User::factory() : null,
            'verified_at' => fake()->boolean(70) ? fake()->dateTimeBetween('-3 months', 'now') : null,
            'verification_notes' => fake()->boolean(30) ? fake()->sentence() : null,
        ];
    }
}
