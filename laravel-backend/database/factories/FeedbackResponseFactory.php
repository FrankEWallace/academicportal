<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FeedbackResponse>
 */
class FeedbackResponseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'feedback_id' => \App\Models\StudentFeedback::factory(),
            'responded_by' => \App\Models\User::factory(),
            'response_message' => fake()->paragraph(2),
            'is_internal_note' => fake()->boolean(20),
            'responded_at' => fake()->dateTimeBetween('-2 months', 'now'),
        ];
    }
}
