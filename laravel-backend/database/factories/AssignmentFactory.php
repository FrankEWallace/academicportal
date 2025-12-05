<?php

namespace Database\Factories;

use App\Models\Assignment;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Assignment>
 */
class AssignmentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Assignment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(3),
            'due_date' => $this->faker->dateTimeBetween('+1 week', '+2 months'),
            'max_score' => $this->faker->numberBetween(50, 100),
            'status' => $this->faker->randomElement(['draft', 'published', 'closed']),
            'is_active' => $this->faker->boolean(90), // 90% chance of being active
        ];
    }

    /**
     * Indicate that the assignment is published.
     */
    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
        ]);
    }

    /**
     * Indicate that the assignment is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
        ]);
    }

    /**
     * Indicate that the assignment is overdue.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_date' => $this->faker->dateTimeBetween('-1 month', '-1 day'),
            'status' => 'published',
        ]);
    }

    /**
     * Indicate that the assignment is upcoming.
     */
    public function upcoming(): static
    {
        return $this->state(fn (array $attributes) => [
            'due_date' => $this->faker->dateTimeBetween('+1 day', '+1 month'),
            'status' => 'published',
        ]);
    }
}
