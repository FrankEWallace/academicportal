<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Hostel;

class HostelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hostels = [
            [
                'name' => 'Unity Hall',
                'code' => 'UH',
                'gender' => 'male',
                'total_rooms' => 120,
                'capacity' => 480,
                'description' => 'Main male hostel with modern facilities and study rooms',
                'location' => 'North Campus',
                'is_active' => true,
            ],
            [
                'name' => 'Excellence Hall',
                'code' => 'EH',
                'gender' => 'female',
                'total_rooms' => 100,
                'capacity' => 400,
                'description' => 'Premier female hostel with attached bathrooms and common rooms',
                'location' => 'North Campus',
                'is_active' => true,
            ],
            [
                'name' => 'Victory Hall',
                'code' => 'VH',
                'gender' => 'male',
                'total_rooms' => 80,
                'capacity' => 320,
                'description' => 'Affordable male hostel near the library',
                'location' => 'South Campus',
                'is_active' => true,
            ],
            [
                'name' => 'Grace Hall',
                'code' => 'GH',
                'gender' => 'female',
                'total_rooms' => 90,
                'capacity' => 360,
                'description' => 'Modern female hostel with WiFi and laundry facilities',
                'location' => 'South Campus',
                'is_active' => true,
            ],
            [
                'name' => 'Legacy Hall',
                'code' => 'LH',
                'gender' => 'male',
                'total_rooms' => 60,
                'capacity' => 240,
                'description' => 'Graduate student male hostel with single rooms',
                'location' => 'East Campus',
                'is_active' => true,
            ],
            [
                'name' => 'Wisdom Hall',
                'code' => 'WH',
                'gender' => 'female',
                'total_rooms' => 70,
                'capacity' => 280,
                'description' => 'Graduate student female hostel near research facilities',
                'location' => 'East Campus',
                'is_active' => true,
            ],
        ];

        foreach ($hostels as $hostel) {
            Hostel::create($hostel);
        }
    }
}
