<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StudentFeedback>
 */
class StudentFeedbackFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = ['academic', 'accommodation', 'fees', 'portal', 'general', 'complaint'];
        $priorities = ['low', 'medium', 'high', 'urgent'];
        $statuses = ['submitted', 'in_review', 'in_progress', 'resolved', 'closed'];
        
        $submittedAt = fake()->dateTimeBetween('-3 months', 'now');
        $status = fake()->randomElement($statuses);
        
        return [
            'student_id' => \App\Models\Student::factory(),
            'ticket_number' => 'FB-' . date('Y') . '-' . str_pad(fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'category' => fake()->randomElement($categories),
            'priority' => fake()->randomElement($priorities),
            'subject' => fake()->sentence(),
            'message' => fake()->paragraph(3),
            'status' => $status,
            'submitted_at' => $submittedAt,
            'last_updated_at' => $status !== 'submitted' ? fake()->dateTimeBetween($submittedAt, 'now') : $submittedAt,
            'resolved_at' => in_array($status, ['resolved', 'closed']) ? fake()->dateTimeBetween($submittedAt, 'now') : null,
            'student_viewed_response' => fake()->boolean(60),
        ];
    }
}
