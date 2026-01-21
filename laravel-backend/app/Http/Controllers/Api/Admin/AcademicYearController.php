<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AcademicYearController extends Controller
{
    /**
     * Get all academic years
     */
    public function index(): JsonResponse
    {
        $years = AcademicYear::with('semesters')->orderBy('start_date', 'desc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Academic years retrieved successfully',
            'data' => ['academic_years' => $years],
        ]);
    }

    /**
     * Get active academic year
     */
    public function active(): JsonResponse
    {
        $activeYear = AcademicYear::where('is_active', true)->with('semesters')->first();

        return response()->json([
            'success' => true,
            'data' => ['academic_year' => $activeYear],
        ]);
    }

    /**
     * Create academic year
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'registration_start_date' => 'nullable|date',
            'registration_end_date' => 'nullable|date|after:registration_start_date',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $year = AcademicYear::create($request->all());

        if ($request->boolean('is_active')) {
            $year->activate();
        }

        return response()->json([
            'success' => true,
            'message' => 'Academic year created successfully',
            'data' => ['academic_year' => $year],
        ], 201);
    }

    /**
     * Get single academic year
     */
    public function show(AcademicYear $academicYear): JsonResponse
    {
        $academicYear->load('semesters');

        return response()->json([
            'success' => true,
            'data' => ['academic_year' => $academicYear],
        ]);
    }

    /**
     * Update academic year
     */
    public function update(Request $request, AcademicYear $academicYear): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            'registration_start_date' => 'nullable|date',
            'registration_end_date' => 'nullable|date',
            'description' => 'nullable|string',
            'is_active' => 'sometimes|boolean',
        ]);

        $academicYear->update($request->except('is_active'));

        if ($request->has('is_active') && $request->boolean('is_active')) {
            $academicYear->activate();
        }

        return response()->json([
            'success' => true,
            'message' => 'Academic year updated successfully',
            'data' => ['academic_year' => $academicYear],
        ]);
    }

    /**
     * Activate academic year
     */
    public function activate(AcademicYear $academicYear): JsonResponse
    {
        $academicYear->activate();

        return response()->json([
            'success' => true,
            'message' => 'Academic year activated successfully',
            'data' => ['academic_year' => $academicYear->fresh()],
        ]);
    }

    /**
     * Delete academic year
     */
    public function destroy(AcademicYear $academicYear): JsonResponse
    {
        if ($academicYear->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete active academic year',
            ], 400);
        }

        $academicYear->delete();

        return response()->json([
            'success' => true,
            'message' => 'Academic year deleted successfully',
        ]);
    }
}

