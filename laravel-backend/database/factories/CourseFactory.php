<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\Department;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    protected $model = Course::class;

    public function definition(): array
    {
        $department = Department::first() ?? Department::factory()->create();
        $teacher = Teacher::factory()->create();

        return [
            'name' => $this->faker->words(3, true),
            'code' => strtoupper($this->faker->bothify('??###')),
            'description' => $this->faker->paragraph(),
            'credits' => $this->faker->numberBetween(1, 4),
            'department_id' => $department->id,
            'teacher_id' => $teacher->id,
            'semester' => $this->faker->numberBetween(1, 8),
            'section' => $this->faker->randomElement(['A', 'B', 'C']),
            'schedule' => [
                [
                    'day' => 'Monday',
                    'time' => '09:00-10:30'
                ],
                [
                    'day' => 'Wednesday',
                    'time' => '09:00-10:30'
                ]
            ],
            'room' => $this->faker->bothify('Room ###'),
            'max_students' => $this->faker->numberBetween(20, 50),
            'start_date' => now()->format('Y-m-d'),
            'end_date' => now()->addMonths(4)->format('Y-m-d'),
            'status' => 'active',
        ];
    }

    public function inactive()
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    public function completed()
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'end_date' => now()->subDays(1)->format('Y-m-d'),
        ]);
    }
}
