<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AccommodationAmenity>
 */
class AccommodationAmenityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amenities = [
            ['name' => 'Wi-Fi', 'description' => 'High-speed wireless internet'],
            ['name' => 'Study Room', 'description' => '24/7 study space with desks'],
            ['name' => 'Laundry', 'description' => 'Washing machines and dryers'],
            ['name' => 'Common Kitchen', 'description' => 'Shared cooking facilities'],
            ['name' => 'Security', 'description' => '24/7 security personnel'],
            ['name' => 'Parking', 'description' => 'Designated parking spaces'],
        ];
        
        $amenity = fake()->randomElement($amenities);
        $hostels = ['Sunrise Hall', 'Sunset Hall', 'Excellence Hall', 'Unity Hall', 'Peace Hall'];
        
        return [
            'hostel_name' => fake()->randomElement($hostels),
            'amenity_name' => $amenity['name'],
            'description' => $amenity['description'],
            'is_available' => fake()->boolean(85),
        ];
    }
}
