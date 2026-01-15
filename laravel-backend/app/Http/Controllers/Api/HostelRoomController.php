<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Hostel;
use App\Models\Room;
use Illuminate\Http\Request;

class HostelRoomController extends Controller
{
    /**
     * Get all available hostels
     */
    public function getHostels(Request $request)
    {
        $query = Hostel::active()->withAvailableSpace();

        // Filter by gender
        if ($request->has('gender')) {
            $query->forGender($request->gender);
        }

        $hostels = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $hostels->map(function ($hostel) {
                return [
                    'id' => $hostel->id,
                    'name' => $hostel->name,
                    'code' => $hostel->code,
                    'gender' => $hostel->gender,
                    'total_rooms' => $hostel->total_rooms,
                    'capacity' => $hostel->capacity,
                    'available_capacity' => $hostel->available_capacity,
                    'occupancy_percentage' => $hostel->occupancy_percentage,
                    'description' => $hostel->description,
                    'location' => $hostel->location,
                    'has_available_space' => $hostel->hasAvailableSpace(),
                ];
            })
        ]);
    }

    /**
     * Get available rooms for a hostel
     */
    public function getAvailableRooms($hostelId, Request $request)
    {
        $hostel = Hostel::active()->findOrFail($hostelId);

        $query = Room::available()->where('hostel_id', $hostelId);

        // Filter by floor
        if ($request->has('floor')) {
            $query->where('floor', $request->floor);
        }

        $rooms = $query->orderBy('floor')
            ->orderBy('room_number')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'hostel' => [
                    'id' => $hostel->id,
                    'name' => $hostel->name,
                    'code' => $hostel->code,
                ],
                'available_rooms' => $rooms->map(function ($room) {
                    return [
                        'id' => $room->id,
                        'room_number' => $room->room_number,
                        'floor' => $room->floor,
                        'capacity' => $room->capacity,
                        'current_occupancy' => $room->current_occupancy,
                        'available_beds' => $room->available_beds,
                        'amenities' => $room->amenities,
                        'status' => $room->status,
                    ];
                })
            ]
        ]);
    }
}
