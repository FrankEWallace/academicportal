<?php

namespace Database\Factories;

use App\Models\Student;
use App\Models\User;
use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    protected $model = Student::class;

    public function definition(): array
    {
        $user = User::factory()->student()->create();
        $department = Department::first() ?? Department::factory()->create();

        return [
            'user_id' => $user->id,
            'student_id' => 'STU' . str_pad($user->id, 6, '0', STR_PAD_LEFT),
            'department_id' => $department->id,
            'admission_date' => $this->faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
            'current_semester' => $this->faker->numberBetween(1, 8),
            'gpa' => $this->faker->randomFloat(2, 2.0, 4.0),
            'status' => 'active',
        ];
    }

    public function inactive()
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'inactive',
        ]);
    }

    public function graduated()
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'graduated',
        ]);
    }
}
