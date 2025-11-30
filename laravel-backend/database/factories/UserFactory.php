<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make('password'), // Default password
            'role' => $this->faker->randomElement(['admin', 'student', 'teacher']),
            'phone' => $this->faker->optional()->phoneNumber(),
            'address' => $this->faker->optional()->address(),
            'date_of_birth' => $this->faker->optional()->date('Y-m-d', '-18 years'),
            'gender' => $this->faker->optional()->randomElement(['male', 'female', 'other']),
            'is_active' => true,
            'remember_token' => Str::random(10),
        ];
    }

    public function admin()
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
        ]);
    }

    public function student()
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'student',
            'program' => $this->faker->randomElement([
                'Bachelor of Computer Science',
                'Bachelor of Information Technology',
                'Bachelor of Software Engineering',
                'Bachelor of Data Science',
                'Bachelor of Business Administration'
            ]),
            'year_level' => $this->faker->randomElement(['1st year', '2nd year', '3rd year', '4th year']),
            'student_status' => $this->faker->randomElement(['active', 'inactive']),
            'enrollment_date' => $this->faker->dateTimeBetween('-4 years', 'now'),
            'current_cgpa' => $this->faker->optional()->randomFloat(2, 2.0, 4.0),
            'bio' => $this->faker->optional()->paragraph(),
            'social_links' => $this->faker->optional()->randomElement([
                [
                    'linkedin' => 'https://linkedin.com/in/' . $this->faker->userName(),
                    'facebook' => 'https://facebook.com/' . $this->faker->userName()
                ],
                [
                    'twitter' => 'https://twitter.com/' . $this->faker->userName(),
                    'instagram' => 'https://instagram.com/' . $this->faker->userName()
                ]
            ]),
        ]);
    }

    public function teacher()
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'teacher',
        ]);
    }

    public function inactive()
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function unverified()
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
