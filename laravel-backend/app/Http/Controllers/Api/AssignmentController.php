<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class AssignmentController extends Controller
{
    /**
     * Display a listing of assignments.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Assignment::with(['course']);

            // Filter by course if provided
            if ($request->has('course_id')) {
                $query->where('course_id', $request->course_id);
            }

            // Filter by status if provided
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by active status
            if ($request->has('active')) {
                $query->where('is_active', $request->boolean('active'));
            }

            // Search by title or description
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Sort by due date or created date
            $sortBy = $request->get('sort_by', 'due_date');
            $sortDirection = $request->get('sort_direction', 'asc');
            $query->orderBy($sortBy, $sortDirection);

            $assignments = $query->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'message' => 'Assignments retrieved successfully',
                'data' => $assignments,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve assignments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created assignment.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'course_id' => 'required|exists:courses,id',
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'due_date' => 'required|date|after:now',
                'max_score' => 'required|integer|min:1|max:1000',
                'status' => 'in:draft,published,closed',
                'is_active' => 'boolean',
            ]);

            // Set defaults
            $validated['status'] = $validated['status'] ?? 'draft';
            $validated['is_active'] = $validated['is_active'] ?? true;

            $assignment = Assignment::create($validated);
            $assignment->load('course');

            return response()->json([
                'success' => true,
                'message' => 'Assignment created successfully',
                'data' => [
                    'assignment' => $assignment
                ]
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create assignment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified assignment.
     */
    public function show(Assignment $assignment): JsonResponse
    {
        try {
            $assignment->load(['course']);

            return response()->json([
                'success' => true,
                'message' => 'Assignment retrieved successfully',
                'data' => [
                    'assignment' => $assignment,
                    'stats' => [
                        'total_submissions' => 0, // Will be implemented when submission system is added
                        'days_until_due' => $assignment->days_until_due,
                        'is_overdue' => $assignment->is_overdue,
                        'formatted_due_date' => $assignment->formatted_due_date,
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve assignment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified assignment.
     */
    public function update(Request $request, Assignment $assignment): JsonResponse
    {
        try {
            $validated = $request->validate([
                'course_id' => 'sometimes|exists:courses,id',
                'title' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'due_date' => 'sometimes|date|after:now',
                'max_score' => 'sometimes|integer|min:1|max:1000',
                'status' => 'sometimes|in:draft,published,closed',
                'is_active' => 'sometimes|boolean',
            ]);

            $assignment->update($validated);
            $assignment->load('course');

            return response()->json([
                'success' => true,
                'message' => 'Assignment updated successfully',
                'data' => [
                    'assignment' => $assignment
                ]
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update assignment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified assignment.
     */
    public function destroy(Assignment $assignment): JsonResponse
    {
        try {
            $assignment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Assignment deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete assignment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get assignments by course.
     */
    public function byCourse(Course $course): JsonResponse
    {
        try {
            $assignments = Assignment::where('course_id', $course->id)
                ->active()
                ->published()
                ->orderBy('due_date', 'asc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Course assignments retrieved successfully',
                'data' => [
                    'course' => $course,
                    'assignments' => $assignments
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve course assignments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get upcoming assignments.
     */
    public function upcoming(Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 10);
            
            $assignments = Assignment::with(['course'])
                ->active()
                ->published()
                ->upcoming()
                ->orderBy('due_date', 'asc')
                ->limit($limit)
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Upcoming assignments retrieved successfully',
                'data' => [
                    'assignments' => $assignments
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve upcoming assignments',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
