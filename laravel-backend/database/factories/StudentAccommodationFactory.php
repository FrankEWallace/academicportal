<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StudentAccommodation>
 */
class StudentAccommodationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $hostels = ['Sunrise Hall', 'Sunset Hall', 'Excellence Hall', 'Unity Hall', 'Peace Hall'];
        $roomTypes = ['single', 'double', 'triple', 'quad'];
        
        $startDate = fake()->dateTimeBetween('-6 months', 'now');
        
        return [
            'student_id' => \App\Models\Student::factory(),
            'academic_year' => '2025/2026',
            'hostel_name' => fake()->randomElement($hostels),
            'room_number' => fake()->numerify('##') . fake()->randomLetter() ,
            'bed_space' => fake()->randomElement(['A', 'B', 'C', 'D']),
            'room_type' => fake()->randomElement($roomTypes),
            'allocation_date' => $startDate,
            'check_in_date' => fake()->dateTimeBetween($startDate, 'now'),
            'check_out_date' => null,
            'renewal_date' => fake()->dateTimeBetween('now', '+3 months'),
            'status' => fake()->randomElement(['active', 'pending_renewal', 'expired']),
        ];
    }
}
