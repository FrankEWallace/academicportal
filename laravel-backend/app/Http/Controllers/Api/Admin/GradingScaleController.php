<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\GradingScale;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GradingScaleController extends Controller
{
    /**
     * Get all grading scales
     */
    public function index(): JsonResponse
    {
        $scales = GradingScale::active()->get();

        return response()->json([
            'success' => true,
            'message' => 'Grading scales retrieved successfully',
            'data' => ['grading_scales' => $scales],
        ]);
    }

    /**
     * Create grading scale
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'grade' => 'required|string|max:10',
            'min_percentage' => 'required|numeric|min:0|max:100',
            'max_percentage' => 'required|numeric|min:0|max:100|gte:min_percentage',
            'grade_point' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'is_passing' => 'sometimes|boolean',
            'order' => 'sometimes|integer',
            'is_active' => 'sometimes|boolean',
        ]);

        $scale = GradingScale::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Grading scale created successfully',
            'data' => ['grading_scale' => $scale],
        ], 201);
    }

    /**
     * Update grading scale
     */
    public function update(Request $request, GradingScale $gradingScale): JsonResponse
    {
        $request->validate([
            'grade' => 'sometimes|string|max:10',
            'min_percentage' => 'sometimes|numeric|min:0|max:100',
            'max_percentage' => 'sometimes|numeric|min:0|max:100',
            'grade_point' => 'sometimes|numeric|min:0',
            'description' => 'nullable|string',
            'is_passing' => 'sometimes|boolean',
            'order' => 'sometimes|integer',
            'is_active' => 'sometimes|boolean',
        ]);

        $gradingScale->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Grading scale updated successfully',
            'data' => ['grading_scale' => $gradingScale],
        ]);
    }

    /**
     * Delete grading scale
     */
    public function destroy(GradingScale $gradingScale): JsonResponse
    {
        $gradingScale->delete();

        return response()->json([
            'success' => true,
            'message' => 'Grading scale deleted successfully',
        ]);
    }

    /**
     * Initialize default grading scales
     */
    public function initializeDefaults(): JsonResponse
    {
        $defaults = [
            ['grade' => 'A+', 'min' => 95, 'max' => 100, 'point' => 4.0, 'order' => 1],
            ['grade' => 'A', 'min' => 90, 'max' => 94.99, 'point' => 4.0, 'order' => 2],
            ['grade' => 'A-', 'min' => 85, 'max' => 89.99, 'point' => 3.7, 'order' => 3],
            ['grade' => 'B+', 'min' => 80, 'max' => 84.99, 'point' => 3.3, 'order' => 4],
            ['grade' => 'B', 'min' => 75, 'max' => 79.99, 'point' => 3.0, 'order' => 5],
            ['grade' => 'B-', 'min' => 70, 'max' => 74.99, 'point' => 2.7, 'order' => 6],
            ['grade' => 'C+', 'min' => 65, 'max' => 69.99, 'point' => 2.3, 'order' => 7],
            ['grade' => 'C', 'min' => 60, 'max' => 64.99, 'point' => 2.0, 'order' => 8],
            ['grade' => 'C-', 'min' => 55, 'max' => 59.99, 'point' => 1.7, 'order' => 9],
            ['grade' => 'D', 'min' => 50, 'max' => 54.99, 'point' => 1.0, 'order' => 10],
            ['grade' => 'F', 'min' => 0, 'max' => 49.99, 'point' => 0.0, 'order' => 11, 'is_passing' => false],
        ];

        foreach ($defaults as $data) {
            GradingScale::firstOrCreate(
                ['grade' => $data['grade']],
                [
                    'min_percentage' => $data['min'],
                    'max_percentage' => $data['max'],
                    'grade_point' => $data['point'],
                    'order' => $data['order'],
                    'is_passing' => $data['is_passing'] ?? true,
                    'is_active' => true,
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Default grading scales initialized successfully',
        ]);
    }
}

