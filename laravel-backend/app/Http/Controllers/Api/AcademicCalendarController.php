<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AcademicCalendar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AcademicCalendarController extends Controller
{
    /**
     * Display a listing of academic calendar events
     */
    public function index(Request $request)
    {
        try {
            $query = AcademicCalendar::query();

            // Filter by academic year
            if ($request->has('academic_year')) {
                $query->where('academic_year', $request->academic_year);
            }

            // Filter by semester
            if ($request->has('semester')) {
                $query->where('semester', $request->semester);
            }

            // Filter by event type
            if ($request->has('event_type')) {
                $query->where('event_type', $request->event_type);
            }

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by date range
            if ($request->has('start_date')) {
                $query->where('start_date', '>=', $request->start_date);
            }

            if ($request->has('end_date')) {
                $query->where('end_date', '<=', $request->end_date);
            }

            // Filter holidays only
            if ($request->has('holidays_only') && $request->holidays_only) {
                $query->where('is_holiday', true);
            }

            $events = $query->orderBy('start_date')
                           ->paginate($request->per_page ?? 50);

            return response()->json([
                'success' => true,
                'data' => $events
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching calendar events',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created calendar event
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'event_type' => 'required|in:semester_start,semester_end,exam_period,registration_period,holiday,break,orientation,graduation,other',
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
                'academic_year' => 'required|integer|min:2020|max:2100',
                'semester' => 'nullable|integer|min:1|max:12',
                'description' => 'nullable|string',
                'is_holiday' => 'nullable|boolean',
                'status' => 'nullable|in:scheduled,ongoing,completed,cancelled'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $event = AcademicCalendar::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Calendar event created successfully',
                'data' => $event
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating calendar event',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified calendar event
     */
    public function show($id)
    {
        try {
            $event = AcademicCalendar::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $event
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Calendar event not found'
            ], 404);
        }
    }

    /**
     * Update the specified calendar event
     */
    public function update(Request $request, $id)
    {
        try {
            $event = AcademicCalendar::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|string|max:255',
                'event_type' => 'sometimes|in:semester_start,semester_end,exam_period,registration_period,holiday,break,orientation,graduation,other',
                'start_date' => 'sometimes|date',
                'end_date' => 'sometimes|date|after_or_equal:start_date',
                'academic_year' => 'sometimes|integer|min:2020|max:2100',
                'semester' => 'nullable|integer|min:1|max:12',
                'description' => 'nullable|string',
                'is_holiday' => 'nullable|boolean',
                'status' => 'nullable|in:scheduled,ongoing,completed,cancelled'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $event->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Calendar event updated successfully',
                'data' => $event
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating calendar event',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified calendar event
     */
    public function destroy($id)
    {
        try {
            $event = AcademicCalendar::findOrFail($id);
            $event->delete();

            return response()->json([
                'success' => true,
                'message' => 'Calendar event deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting calendar event',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get upcoming events
     */
    public function upcoming(Request $request)
    {
        try {
            $limit = $request->limit ?? 10;
            $today = Carbon::today();

            $events = AcademicCalendar::where('start_date', '>=', $today)
                ->whereIn('status', ['scheduled', 'ongoing'])
                ->orderBy('start_date')
                ->limit($limit)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $events
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching upcoming events',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get events for a specific semester
     */
    public function bySemester($semester, Request $request)
    {
        try {
            $academicYear = $request->academic_year ?? date('Y');

            $events = AcademicCalendar::where('semester', $semester)
                ->where('academic_year', $academicYear)
                ->orderBy('start_date')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $events
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching semester events',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current/active events
     */
    public function current()
    {
        try {
            $today = Carbon::today();

            $events = AcademicCalendar::where('start_date', '<=', $today)
                ->where('end_date', '>=', $today)
                ->where('status', 'ongoing')
                ->orderBy('start_date')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $events
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching current events',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get holidays
     */
    public function holidays(Request $request)
    {
        try {
            $academicYear = $request->academic_year ?? date('Y');

            $holidays = AcademicCalendar::where('is_holiday', true)
                ->where('academic_year', $academicYear)
                ->orderBy('start_date')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $holidays
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching holidays',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if a date is a holiday
     */
    public function checkHoliday(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'date' => 'required|date'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            $date = Carbon::parse($request->date);

            $holiday = AcademicCalendar::where('is_holiday', true)
                ->where('start_date', '<=', $date)
                ->where('end_date', '>=', $date)
                ->first();

            return response()->json([
                'success' => true,
                'is_holiday' => $holiday ? true : false,
                'event' => $holiday
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error checking holiday',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get academic year overview
     */
    public function yearOverview($academicYear)
    {
        try {
            $events = AcademicCalendar::where('academic_year', $academicYear)
                ->orderBy('start_date')
                ->get();

            $groupedEvents = $events->groupBy('event_type');

            $overview = [
                'academic_year' => $academicYear,
                'total_events' => $events->count(),
                'total_holidays' => $events->where('is_holiday', true)->count(),
                'events_by_type' => $groupedEvents->map(function ($items) {
                    return $items->count();
                }),
                'semesters' => $events->whereNotNull('semester')->pluck('semester')->unique()->values(),
                'timeline' => $events
            ];

            return response()->json([
                'success' => true,
                'data' => $overview
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching year overview',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
