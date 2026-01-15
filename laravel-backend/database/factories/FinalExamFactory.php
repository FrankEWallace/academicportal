<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FinalExam>
 */
class FinalExamFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $score = fake()->randomFloat(2, 0, 100);
        
        return [
            'student_id' => \App\Models\Student::factory(),
            'course_id' => \App\Models\Course::factory(),
            'semester_code' => '2025/2026-1',
            'exam_score' => $score,
            'max_score' => 100,
            'weight_percentage' => 60,
            'exam_date' => fake()->dateTimeBetween('-2 months', '-1 month'),
            'grade' => $score >= 70 ? 'A' : ($score >= 60 ? 'B' : ($score >= 50 ? 'C' : ($score >= 45 ? 'D' : ($score >= 40 ? 'E' : 'F')))),
            'remarks' => fake()->boolean(15) ? fake()->sentence() : null,
        ];
    }
}
