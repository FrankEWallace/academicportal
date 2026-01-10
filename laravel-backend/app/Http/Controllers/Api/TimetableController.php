<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Timetable;
use App\Models\Course;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class TimetableController extends Controller
{
    /**
     * Display a listing of timetables with filters
     */
    public function index(Request $request)
    {
        try {
            $query = Timetable::with(['course', 'teacher.user']);

            // Filter by semester and academic year
            if ($request->has('semester')) {
                $query->where('semester', $request->semester);
            }

            if ($request->has('academic_year')) {
                $query->where('academic_year', $request->academic_year);
            }

            // Filter by day of week
            if ($request->has('day_of_week')) {
                $query->where('day_of_week', $request->day_of_week);
            }

            // Filter by course
            if ($request->has('course_id')) {
                $query->where('course_id', $request->course_id);
            }

            // Filter by teacher
            if ($request->has('teacher_id')) {
                $query->where('teacher_id', $request->teacher_id);
            }

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by building/room
            if ($request->has('building')) {
                $query->where('building', $request->building);
            }

            if ($request->has('room_number')) {
                $query->where('room_number', $request->room_number);
            }

            $timetables = $query->orderBy('day_of_week')
                               ->orderBy('start_time')
                               ->paginate($request->per_page ?? 50);

            return response()->json([
                'success' => true,
                'data' => $timetables
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching timetables',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created timetable
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'course_id' => 'required|exists:courses,id',
                'teacher_id' => 'required|exists:teachers,id',
                'day_of_week' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
                'start_time' => 'required|date_format:H:i',
                'end_time' => 'required|date_format:H:i|after:start_time',
                'semester' => 'required|integer|min:1',
                'academic_year' => 'required|integer|min:2020|max:2100',
                'room_number' => 'nullable|string|max:50',
                'building' => 'nullable|string|max:100',
                'section' => 'nullable|string|max:10',
                'capacity' => 'nullable|integer|min:1|max:500',
                'status' => 'nullable|in:active,cancelled,completed',
                'notes' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check for conflicts
            $conflicts = $this->checkConflicts($request->all());
            if (!empty($conflicts)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Schedule conflicts detected',
                    'conflicts' => $conflicts
                ], 409);
            }

            $timetable = Timetable::create($request->all());
            $timetable->load(['course', 'teacher.user']);

            return response()->json([
                'success' => true,
                'message' => 'Timetable created successfully',
                'data' => $timetable
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating timetable',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified timetable
     */
    public function show($id)
    {
        try {
            $timetable = Timetable::with(['course', 'teacher.user'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $timetable
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Timetable not found'
            ], 404);
        }
    }

    /**
     * Update the specified timetable
     */
    public function update(Request $request, $id)
    {
        try {
            $timetable = Timetable::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'course_id' => 'sometimes|exists:courses,id',
                'teacher_id' => 'sometimes|exists:teachers,id',
                'day_of_week' => 'sometimes|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
                'start_time' => 'sometimes|date_format:H:i',
                'end_time' => 'sometimes|date_format:H:i|after:start_time',
                'semester' => 'sometimes|integer|min:1',
                'academic_year' => 'sometimes|integer|min:2020|max:2100',
                'room_number' => 'nullable|string|max:50',
                'building' => 'nullable|string|max:100',
                'section' => 'nullable|string|max:10',
                'capacity' => 'nullable|integer|min:1|max:500',
                'enrolled_count' => 'nullable|integer|min:0',
                'status' => 'nullable|in:active,cancelled,completed',
                'notes' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check for conflicts (excluding current timetable)
            $conflicts = $this->checkConflicts($request->all(), $id);
            if (!empty($conflicts)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Schedule conflicts detected',
                    'conflicts' => $conflicts
                ], 409);
            }

            $timetable->update($request->all());
            $timetable->load(['course', 'teacher.user']);

            return response()->json([
                'success' => true,
                'message' => 'Timetable updated successfully',
                'data' => $timetable
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating timetable',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified timetable
     */
    public function destroy($id)
    {
        try {
            $timetable = Timetable::findOrFail($id);
            $timetable->delete();

            return response()->json([
                'success' => true,
                'message' => 'Timetable deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting timetable',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get student's timetable
     */
    public function studentTimetable($studentId, Request $request)
    {
        try {
            $semester = $request->semester ?? 1;
            $academicYear = $request->academic_year ?? date('Y');

            $timetables = DB::table('timetables')
                ->join('courses', 'timetables.course_id', '=', 'courses.id')
                ->join('enrollments', 'courses.id', '=', 'enrollments.course_id')
                ->join('teachers', 'timetables.teacher_id', '=', 'teachers.id')
                ->join('users', 'teachers.user_id', '=', 'users.id')
                ->where('enrollments.student_id', $studentId)
                ->where('timetables.semester', $semester)
                ->where('timetables.academic_year', $academicYear)
                ->where('timetables.status', 'active')
                ->select(
                    'timetables.*',
                    'courses.name as course_name',
                    'courses.code as course_code',
                    'users.name as teacher_name'
                )
                ->orderBy('timetables.day_of_week')
                ->orderBy('timetables.start_time')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $timetables
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching student timetable',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get teacher's timetable
     */
    public function teacherTimetable($teacherId, Request $request)
    {
        try {
            $semester = $request->semester ?? 1;
            $academicYear = $request->academic_year ?? date('Y');

            $timetables = Timetable::with(['course'])
                ->where('teacher_id', $teacherId)
                ->where('semester', $semester)
                ->where('academic_year', $academicYear)
                ->where('status', 'active')
                ->orderBy('day_of_week')
                ->orderBy('start_time')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $timetables
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching teacher timetable',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get room schedule
     */
    public function roomSchedule(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'building' => 'required|string',
                'room_number' => 'required|string',
                'semester' => 'nullable|integer',
                'academic_year' => 'nullable|integer'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $semester = $request->semester ?? 1;
            $academicYear = $request->academic_year ?? date('Y');

            $schedules = Timetable::with(['course', 'teacher.user'])
                ->where('building', $request->building)
                ->where('room_number', $request->room_number)
                ->where('semester', $semester)
                ->where('academic_year', $academicYear)
                ->where('status', 'active')
                ->orderBy('day_of_week')
                ->orderBy('start_time')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $schedules
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching room schedule',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check for scheduling conflicts
     */
    private function checkConflicts($data, $excludeId = null)
    {
        $conflicts = [];

        // Extract relevant data
        $teacherId = $data['teacher_id'] ?? null;
        $dayOfWeek = $data['day_of_week'] ?? null;
        $startTime = $data['start_time'] ?? null;
        $endTime = $data['end_time'] ?? null;
        $building = $data['building'] ?? null;
        $roomNumber = $data['room_number'] ?? null;
        $semester = $data['semester'] ?? null;
        $academicYear = $data['academic_year'] ?? null;

        if (!$teacherId || !$dayOfWeek || !$startTime || !$endTime) {
            return $conflicts;
        }

        // Check teacher conflicts
        $teacherConflict = Timetable::where('teacher_id', $teacherId)
            ->where('day_of_week', $dayOfWeek)
            ->where('semester', $semester)
            ->where('academic_year', $academicYear)
            ->where('status', 'active')
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where(function ($q) use ($startTime, $endTime) {
                    $q->where('start_time', '<=', $startTime)
                      ->where('end_time', '>', $startTime);
                })->orWhere(function ($q) use ($startTime, $endTime) {
                    $q->where('start_time', '<', $endTime)
                      ->where('end_time', '>=', $endTime);
                })->orWhere(function ($q) use ($startTime, $endTime) {
                    $q->where('start_time', '>=', $startTime)
                      ->where('end_time', '<=', $endTime);
                });
            })
            ->when($excludeId, function ($query) use ($excludeId) {
                return $query->where('id', '!=', $excludeId);
            })
            ->first();

        if ($teacherConflict) {
            $conflicts[] = [
                'type' => 'teacher_conflict',
                'message' => 'Teacher already has a class at this time',
                'conflicting_schedule' => $teacherConflict
            ];
        }

        // Check room conflicts
        if ($building && $roomNumber) {
            $roomConflict = Timetable::where('building', $building)
                ->where('room_number', $roomNumber)
                ->where('day_of_week', $dayOfWeek)
                ->where('semester', $semester)
                ->where('academic_year', $academicYear)
                ->where('status', 'active')
                ->where(function ($query) use ($startTime, $endTime) {
                    $query->where(function ($q) use ($startTime, $endTime) {
                        $q->where('start_time', '<=', $startTime)
                          ->where('end_time', '>', $startTime);
                    })->orWhere(function ($q) use ($startTime, $endTime) {
                        $q->where('start_time', '<', $endTime)
                          ->where('end_time', '>=', $endTime);
                    })->orWhere(function ($q) use ($startTime, $endTime) {
                        $q->where('start_time', '>=', $startTime)
                          ->where('end_time', '<=', $endTime);
                    });
                })
                ->when($excludeId, function ($query) use ($excludeId) {
                    return $query->where('id', '!=', $excludeId);
                })
                ->first();

            if ($roomConflict) {
                $conflicts[] = [
                    'type' => 'room_conflict',
                    'message' => 'Room already booked at this time',
                    'conflicting_schedule' => $roomConflict
                ];
            }
        }

        return $conflicts;
    }

    /**
     * Get all available time slots for a specific day
     */
    public function availableSlots(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'day_of_week' => 'required|in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
                'semester' => 'required|integer',
                'academic_year' => 'required|integer',
                'building' => 'nullable|string',
                'room_number' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $bookedSlots = Timetable::where('day_of_week', $request->day_of_week)
                ->where('semester', $request->semester)
                ->where('academic_year', $request->academic_year)
                ->where('status', 'active')
                ->when($request->building, function ($q) use ($request) {
                    return $q->where('building', $request->building);
                })
                ->when($request->room_number, function ($q) use ($request) {
                    return $q->where('room_number', $request->room_number);
                })
                ->select('start_time', 'end_time', 'room_number', 'building')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'booked_slots' => $bookedSlots,
                    'available_hours' => '08:00-20:00' // Configurable
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching available slots',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
