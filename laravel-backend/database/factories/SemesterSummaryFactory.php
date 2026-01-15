<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SemesterSummary>
 */
class SemesterSummaryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $semesterGPA = fake()->randomFloat(2, 2.0, 5.0);
        $cumulativeGPA = fake()->randomFloat(2, 2.5, 5.0);
        
        return [
            'student_id' => \App\Models\Student::factory(),
            'semester_code' => '2025/2026-1',
            'total_units_registered' => fake()->numberBetween(15, 24),
            'total_units_passed' => fake()->numberBetween(12, 24),
            'semester_gpa' => $semesterGPA,
            'cumulative_gpa' => $cumulativeGPA,
            'academic_standing' => $cumulativeGPA >= 3.5 ? 'excellent' : ($cumulativeGPA >= 3.0 ? 'good' : ($cumulativeGPA >= 2.5 ? 'satisfactory' : 'probation')),
            'remarks' => fake()->boolean(30) ? fake()->sentence() : null,
        ];
    }
}
