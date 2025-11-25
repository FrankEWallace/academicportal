<?php

namespace Database\Factories;

use App\Models\Enrollment;
use App\Models\Student;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

class EnrollmentFactory extends Factory
{
    protected $model = Enrollment::class;

    public function definition(): array
    {
        return [
            'student_id' => Student::factory(),
            'course_id' => Course::factory(),
            'enrollment_date' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'status' => $this->faker->randomElement(['enrolled', 'completed', 'dropped', 'withdrawn']),
            'grade' => $this->faker->optional()->randomElement(['A+', 'A', 'A-', 'B+', 'B', 'B-', 'C+', 'C', 'C-', 'D', 'F']),
            'credits_earned' => function (array $attributes) {
                return $attributes['status'] === 'completed' ? 
                    $this->faker->numberBetween(1, 4) : null;
            },
        ];
    }

    public function enrolled()
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'enrolled',
            'grade' => null,
            'credits_earned' => null,
        ]);
    }

    public function completed()
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'grade' => $this->faker->randomElement(['A+', 'A', 'A-', 'B+', 'B', 'B-', 'C+', 'C']),
            'credits_earned' => $this->faker->numberBetween(1, 4),
        ]);
    }

    public function dropped()
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'dropped',
            'grade' => null,
            'credits_earned' => null,
        ]);
    }
}
