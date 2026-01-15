<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ContinuousAssessment>
 */
class ContinuousAssessmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['quiz', 'assignment', 'midterm', 'project'];
        $maxScores = ['quiz' => 10, 'assignment' => 20, 'midterm' => 30, 'project' => 40];
        $type = fake()->randomElement($types);
        $maxScore = $maxScores[$type];
        
        return [
            'student_id' => \App\Models\Student::factory(),
            'course_id' => \App\Models\Course::factory(),
            'semester_code' => '2025/2026-1',
            'assessment_type' => $type,
            'assessment_name' => ucfirst($type) . ' ' . fake()->numberBetween(1, 5),
            'score' => fake()->randomFloat(2, 0, $maxScore),
            'max_score' => $maxScore,
            'weight_percentage' => fake()->randomElement([10, 15, 20, 25, 30]),
            'assessment_date' => fake()->dateTimeBetween('-3 months', 'now'),
            'remarks' => fake()->boolean(20) ? fake()->sentence() : null,
        ];
    }
}
