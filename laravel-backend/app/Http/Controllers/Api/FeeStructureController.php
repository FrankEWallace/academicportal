<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FeeStructure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class FeeStructureController extends Controller
{
    /**
     * Display a listing of fee structures
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = FeeStructure::query();

            // Apply filters
            if ($request->has('program')) {
                $query->forProgram($request->program);
            }

            if ($request->has('semester')) {
                $query->forSemester($request->semester);
            }

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('fee_type')) {
                $query->where('fee_type', $request->fee_type);
            }

            // Sort by due date by default
            $query->orderBy('due_date', 'asc');

            // Pagination
            $perPage = $request->get('per_page', 15);
            $feeStructures = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $feeStructures,
                'message' => 'Fee structures retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving fee structures',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created fee structure
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'program' => 'required|string|max:255',
                'semester' => 'required|integer|min:1|max:8',
                'amount' => 'required|numeric|min:0',
                'due_date' => 'required|date',
                'fee_type' => 'required|string|max:255',
                'description' => 'nullable|string',
                'status' => 'in:active,inactive'
            ]);

            $feeStructure = FeeStructure::create($validated);

            return response()->json([
                'success' => true,
                'data' => $feeStructure,
                'message' => 'Fee structure created successfully'
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
                'message' => 'Error creating fee structure',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified fee structure
     */
    public function show(string $id): JsonResponse
    {
        try {
            $feeStructure = FeeStructure::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $feeStructure,
                'message' => 'Fee structure retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Fee structure not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified fee structure
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $feeStructure = FeeStructure::findOrFail($id);

            $validated = $request->validate([
                'program' => 'sometimes|required|string|max:255',
                'semester' => 'sometimes|required|integer|min:1|max:8',
                'amount' => 'sometimes|required|numeric|min:0',
                'due_date' => 'sometimes|required|date',
                'fee_type' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'status' => 'sometimes|in:active,inactive'
            ]);

            $feeStructure->update($validated);

            return response()->json([
                'success' => true,
                'data' => $feeStructure->fresh(),
                'message' => 'Fee structure updated successfully'
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
                'message' => 'Error updating fee structure',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified fee structure
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $feeStructure = FeeStructure::findOrFail($id);
            $feeStructure->delete();

            return response()->json([
                'success' => true,
                'message' => 'Fee structure deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting fee structure',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get fee structures by program and semester
     */
    public function getByProgramSemester(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'program' => 'required|string',
                'semester' => 'required|integer'
            ]);

            $feeStructures = FeeStructure::active()
                ->forProgram($request->program)
                ->forSemester($request->semester)
                ->orderBy('fee_type')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $feeStructures,
                'message' => 'Fee structures retrieved successfully'
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
                'message' => 'Error retrieving fee structures',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get overdue fee structures
     */
    public function getOverdue(): JsonResponse
    {
        try {
            $overdueFees = FeeStructure::active()
                ->dueBefore(now())
                ->orderBy('due_date', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $overdueFees,
                'message' => 'Overdue fee structures retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving overdue fee structures',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
