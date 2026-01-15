<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Room;
use App\Models\Hostel;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hostels = Hostel::all();

        foreach ($hostels as $hostel) {
            $roomsPerFloor = [
                'UH' => 40, // Unity Hall: 3 floors
                'EH' => 33, // Excellence Hall: 3 floors
                'VH' => 27, // Victory Hall: 3 floors
                'GH' => 30, // Grace Hall: 3 floors
                'LH' => 20, // Legacy Hall: 3 floors
                'WH' => 23, // Wisdom Hall: 3 floors
            ];

            $floors = [
                'UH' => 3,
                'EH' => 3,
                'VH' => 3,
                'GH' => 3,
                'LH' => 3,
                'WH' => 3,
            ];

            $capacitiesPerHostel = [
                'UH' => [4, 4, 4, 4], // Mostly 4-person rooms
                'EH' => [4, 4, 4, 4],
                'VH' => [4, 4, 4, 4],
                'GH' => [4, 4, 4, 4],
                'LH' => [1, 1, 2, 4], // Mix of single, double, quad for graduates
                'WH' => [1, 1, 2, 4],
            ];

            $floorCount = $floors[$hostel->code];
            $roomsPerFloorCount = $roomsPerFloor[$hostel->code];
            $capacities = $capacitiesPerHostel[$hostel->code];

            $roomNumber = 1;

            for ($floor = 1; $floor <= $floorCount; $floor++) {
                for ($room = 1; $room <= $roomsPerFloorCount; $room++) {
                    $capacity = $capacities[array_rand($capacities)];
                    $roomCode = sprintf('%s%d%02d', $hostel->code, $floor, $room);

                    Room::create([
                        'hostel_id' => $hostel->id,
                        'room_number' => $roomCode,
                        'floor' => $floor,
                        'capacity' => $capacity,
                        'current_occupancy' => 0,
                        'status' => 'available',
                    ]);

                    $roomNumber++;
                }
            }
        }
    }
}
