<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement([
                'Computer Science',
                'Mathematics',
                'Physics',
                'Chemistry',
                'Biology',
                'English Literature',
                'Business Administration',
                'Mechanical Engineering',
                'Electrical Engineering',
                'Psychology'
            ]),
            'code' => strtoupper($this->faker->unique()->bothify('???')),
            'description' => $this->faker->paragraph(),
            'head_id' => null, // Will be set later if needed
            'established_year' => $this->faker->numberBetween(1950, 2020),
            'budget' => $this->faker->numberBetween(100000, 1000000),
            'location' => $this->faker->words(2, true) . ' Building',
        ];
    }
}
