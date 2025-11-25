<?php

namespace Database\Factories;

use App\Models\Teacher;
use App\Models\User;
use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

class TeacherFactory extends Factory
{
    protected $model = Teacher::class;

    public function definition(): array
    {
        $user = User::factory()->teacher()->create();
        $department = Department::first() ?? Department::factory()->create();

        return [
            'user_id' => $user->id,
            'employee_id' => 'EMP' . str_pad($user->id, 6, '0', STR_PAD_LEFT),
            'department_id' => $department->id,
            'designation' => $this->faker->randomElement(['Professor', 'Associate Professor', 'Assistant Professor', 'Lecturer']),
            'qualification' => $this->faker->randomElement(['PhD', 'Masters', 'Bachelors']),
            'specialization' => $this->faker->words(2, true),
            'joining_date' => $this->faker->dateTimeBetween('-10 years', 'now')->format('Y-m-d'),
            'status' => 'active',
        ];
    }

    public function inactive()
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    public function professor()
    {
        return $this->state(fn (array $attributes) => [
            'designation' => 'Professor',
            'qualification' => 'PhD',
        ]);
    }
}
