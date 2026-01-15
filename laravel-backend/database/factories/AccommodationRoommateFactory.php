<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AccommodationRoommate>
 */
class AccommodationRoommateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'accommodation_id' => \App\Models\StudentAccommodation::factory(),
            'roommate_student_id' => \App\Models\Student::factory(),
            'roommate_name' => fake()->name(),
            'roommate_phone' => fake()->phoneNumber(),
            'roommate_email' => fake()->safeEmail(),
            'bed_space' => fake()->randomElement(['A', 'B', 'C', 'D']),
        ];
    }
}
