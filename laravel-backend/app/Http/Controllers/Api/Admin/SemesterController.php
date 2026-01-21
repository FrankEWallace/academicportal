<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SemesterController extends Controller
{
    /**
     * Get all semesters
     */
    public function index(Request $request): JsonResponse
    {
        $query = Semester::with('academicYear');

        if ($request->has('academic_year_id')) {
            $query->where('academic_year_id', $request->query('academic_year_id'));
        }

        $semesters = $query->orderBy('start_date', 'desc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Semesters retrieved successfully',
            'data' => ['semesters' => $semesters],
        ]);
    }

    /**
     * Get active semester
     */
    public function active(): JsonResponse
    {
        $activeSemester = Semester::where('is_active', true)->with('academicYear')->first();

        return response()->json([
            'success' => true,
            'data' => ['semester' => $activeSemester],
        ]);
    }

    /**
     * Create semester
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'name' => 'required|string|max:255',
            'semester_number' => 'required|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'registration_start_date' => 'nullable|date',
            'registration_end_date' => 'nullable|date',
            'add_drop_deadline' => 'nullable|date',
            'exam_start_date' => 'nullable|date',
            'exam_end_date' => 'nullable|date|after:exam_start_date',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $semester = Semester::create($request->all());

        if ($request->boolean('is_active')) {
            $semester->activate();
        }

        return response()->json([
            'success' => true,
            'message' => 'Semester created successfully',
            'data' => ['semester' => $semester->load('academicYear')],
        ], 201);
    }

    /**
     * Get single semester
     */
    public function show(Semester $semester): JsonResponse
    {
        $semester->load('academicYear');

        return response()->json([
            'success' => true,
            'data' => ['semester' => $semester],
        ]);
    }

    /**
     * Update semester
     */
    public function update(Request $request, Semester $semester): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'semester_number' => 'sometimes|integer|min:1',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date',
            'registration_start_date' => 'nullable|date',
            'registration_end_date' => 'nullable|date',
            'add_drop_deadline' => 'nullable|date',
            'exam_start_date' => 'nullable|date',
            'exam_end_date' => 'nullable|date',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $semester->update($request->except('is_active'));

        if ($request->has('is_active') && $request->boolean('is_active')) {
            $semester->activate();
        }

        return response()->json([
            'success' => true,
            'message' => 'Semester updated successfully',
            'data' => ['semester' => $semester->load('academicYear')],
        ]);
    }

    /**
     * Activate semester
     */
    public function activate(Semester $semester): JsonResponse
    {
        $semester->activate();

        return response()->json([
            'success' => true,
            'message' => 'Semester activated successfully',
            'data' => ['semester' => $semester->fresh()->load('academicYear')],
        ]);
    }

    /**
     * Delete semester
     */
    public function destroy(Semester $semester): JsonResponse
    {
        if ($semester->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete active semester',
            ], 400);
        }

        $semester->delete();

        return response()->json([
            'success' => true,
            'message' => 'Semester deleted successfully',
        ]);
    }
}

