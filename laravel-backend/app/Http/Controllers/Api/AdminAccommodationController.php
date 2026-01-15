<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Hostel;
use App\Models\Room;
use App\Models\StudentAccommodation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AdminAccommodationController extends Controller
{
    /**
     * Get all hostels
     */
    public function getHostels(Request $request)
    {
        $query = Hostel::withCount('rooms');

        if ($request->has('gender')) {
            $query->forGender($request->gender);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active === 'true');
        }

        $hostels = $query->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $hostels
        ]);
    }

    /**
     * Get all rooms with filters
     */
    public function getRooms(Request $request)
    {
        $query = Room::with('hostel');

        if ($request->has('hostel_id')) {
            $query->where('hostel_id', $request->hostel_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('floor')) {
            $query->where('floor', $request->floor);
        }

        if ($request->has('available_only')) {
            $query->available();
        }

        $rooms = $query->orderBy('hostel_id')
            ->orderBy('floor')
            ->orderBy('room_number')
            ->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => $rooms
        ]);
    }

    /**
     * Get pending allocations
     */
    public function getPendingAllocations()
    {
        $accommodations = StudentAccommodation::pending()
            ->with(['student.user'])
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $accommodations
        ]);
    }

    /**
     * Get accommodation details
     */
    public function show($id)
    {
        $accommodation = StudentAccommodation::with([
            'student.user',
            'room.hostel',
            'allocatedBy',
            'vacatedBy'
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $accommodation
        ]);
    }

    /**
     * Allocate accommodation
     */
    public function allocate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'room_id' => 'required|exists:rooms,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $accommodation = StudentAccommodation::findOrFail($id);
        $adminId = Auth::id();

        if ($accommodation->status === 'allocated') {
            return response()->json([
                'success' => false,
                'message' => 'Accommodation already allocated'
            ], 400);
        }

        $room = Room::find($request->room_id);
        if (!$room || !$room->isAvailable()) {
            return response()->json([
                'success' => false,
                'message' => 'Room is not available'
            ], 400);
        }

        $success = $accommodation->allocate($request->room_id, $adminId);

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to allocate accommodation'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Accommodation allocated successfully',
            'data' => $accommodation->fresh()
        ]);
    }

    /**
     * Vacate accommodation
     */
    public function vacate($id)
    {
        $accommodation = StudentAccommodation::findOrFail($id);
        $adminId = Auth::id();

        if ($accommodation->status !== 'allocated') {
            return response()->json([
                'success' => false,
                'message' => 'Accommodation is not currently allocated'
            ], 400);
        }

        $accommodation->vacate($adminId);

        return response()->json([
            'success' => true,
            'message' => 'Accommodation vacated successfully',
            'data' => $accommodation->fresh()
        ]);
    }

    /**
     * Bulk allocate accommodations
     */
    public function bulkAllocate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'allocations' => 'required|array',
            'allocations.*.accommodation_id' => 'required|exists:student_accommodations,id',
            'allocations.*.room_id' => 'required|exists:rooms,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $adminId = Auth::id();
        $allocated = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($request->allocations as $allocationData) {
                $accommodation = StudentAccommodation::find($allocationData['accommodation_id']);
                $room = Room::find($allocationData['room_id']);

                if ($accommodation->status === 'allocated') {
                    $errors[] = "Accommodation {$allocationData['accommodation_id']}: Already allocated";
                    continue;
                }

                if (!$room->isAvailable()) {
                    $errors[] = "Room {$allocationData['room_id']}: Not available";
                    continue;
                }

                $success = $accommodation->allocate($allocationData['room_id'], $adminId);
                if ($success) {
                    $allocated++;
                } else {
                    $errors[] = "Accommodation {$allocationData['accommodation_id']}: Allocation failed";
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Allocated {$allocated} accommodation(s)",
                'data' => [
                    'allocated' => $allocated,
                    'errors' => $errors
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error allocating accommodations: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get hostel occupancy
     */
    public function getHostelOccupancy($hostelId)
    {
        $hostel = Hostel::with(['rooms' => function ($query) {
            $query->withCount('currentOccupants');
        }])->findOrFail($hostelId);

        $occupancy = [
            'hostel' => $hostel->name,
            'total_rooms' => $hostel->total_rooms,
            'capacity' => $hostel->capacity,
            'available_capacity' => $hostel->available_capacity,
            'occupancy_percentage' => $hostel->occupancy_percentage,
            'rooms' => $hostel->rooms->map(function ($room) {
                return [
                    'id' => $room->id,
                    'room_number' => $room->room_number,
                    'floor' => $room->floor,
                    'capacity' => $room->capacity,
                    'current_occupancy' => $room->current_occupancy,
                    'available_beds' => $room->available_beds,
                    'status' => $room->status,
                ];
            })
        ];

        return response()->json([
            'success' => true,
            'data' => $occupancy
        ]);
    }

    /**
     * Get available rooms
     */
    public function getAvailableRooms(Request $request)
    {
        $query = Room::available()->with('hostel');

        if ($request->has('hostel_id')) {
            $query->where('hostel_id', $request->hostel_id);
        }

        if ($request->has('gender')) {
            $query->whereHas('hostel', function ($q) use ($request) {
                $q->where('gender', $request->gender);
            });
        }

        if ($request->has('floor')) {
            $query->where('floor', $request->floor);
        }

        $rooms = $query->orderBy('hostel_id')
            ->orderBy('floor')
            ->orderBy('room_number')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $rooms
        ]);
    }

    /**
     * Get accommodation statistics
     */
    public function statistics(Request $request)
    {
        $accommodationQuery = StudentAccommodation::query();

        if ($request->has('academic_year')) {
            $accommodationQuery->where('academic_year', $request->academic_year);
        }

        $stats = [
            'total_accommodations' => (clone $accommodationQuery)->count(),
            'pending_allocation' => (clone $accommodationQuery)->pending()->count(),
            'allocated' => (clone $accommodationQuery)->allocated()->count(),
            'vacated' => (clone $accommodationQuery)->where('status', 'vacated')->count(),
            'total_hostels' => Hostel::active()->count(),
            'total_rooms' => Room::count(),
            'available_rooms' => Room::available()->count(),
            'occupied_rooms' => Room::where('status', 'occupied')->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
