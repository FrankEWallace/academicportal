<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Student;
use App\Models\FeeStructure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Invoice::with(['student.user', 'feeStructure', 'payments']);

            // Apply filters
            if ($request->has('student_id')) {
                $query->forStudent($request->student_id);
            }

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('overdue') && $request->overdue) {
                $query->overdue();
            }

            if ($request->has('due_date_from')) {
                $query->where('due_date', '>=', $request->due_date_from);
            }

            if ($request->has('due_date_to')) {
                $query->where('due_date', '<=', $request->due_date_to);
            }

            // Sort by due date by default
            $query->orderBy('due_date', 'desc');

            // Pagination
            $perPage = $request->get('per_page', 15);
            $invoices = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $invoices,
                'message' => 'Invoices retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving invoices',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created invoice
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'student_id' => 'required|exists:students,id',
                'fee_structure_id' => 'required|exists:fee_structures,id',
                'due_date' => 'nullable|date',
                'description' => 'nullable|string',
                'notes' => 'nullable|string',
            ]);

            DB::beginTransaction();

            // Get fee structure to set amount
            $feeStructure = FeeStructure::findOrFail($validated['fee_structure_id']);
            
            // Set due date from fee structure if not provided
            if (!isset($validated['due_date'])) {
                $validated['due_date'] = $feeStructure->due_date;
            }

            $invoice = Invoice::create([
                'student_id' => $validated['student_id'],
                'fee_structure_id' => $validated['fee_structure_id'],
                'amount_due' => $feeStructure->amount,
                'due_date' => $validated['due_date'],
                'description' => $validated['description'] ?? $feeStructure->description,
                'notes' => $validated['notes'] ?? null,
            ]);

            $invoice->load(['student.user', 'feeStructure']);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $invoice,
                'message' => 'Invoice created successfully'
            ], 201);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error creating invoice',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified invoice
     */
    public function show(string $id): JsonResponse
    {
        try {
            $invoice = Invoice::with(['student.user', 'feeStructure', 'payments.processedBy'])
                             ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $invoice,
                'message' => 'Invoice retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified invoice
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $invoice = Invoice::findOrFail($id);

            $validated = $request->validate([
                'due_date' => 'sometimes|required|date',
                'description' => 'nullable|string',
                'notes' => 'nullable|string',
                'status' => 'sometimes|in:pending,partial,paid,overdue,cancelled'
            ]);

            $invoice->update($validated);

            $invoice->load(['student.user', 'feeStructure', 'payments']);

            return response()->json([
                'success' => true,
                'data' => $invoice,
                'message' => 'Invoice updated successfully'
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
                'message' => 'Error updating invoice',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified invoice
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $invoice = Invoice::findOrFail($id);
            
            // Check if invoice has payments
            if ($invoice->payments()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete invoice with existing payments'
                ], 422);
            }

            $invoice->delete();

            return response()->json([
                'success' => true,
                'message' => 'Invoice deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting invoice',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get invoices for a specific student
     */
    public function getStudentInvoices(Request $request, string $studentId): JsonResponse
    {
        try {
            $query = Invoice::with(['feeStructure', 'payments'])
                           ->forStudent($studentId);

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            $query->orderBy('due_date', 'desc');

            $invoices = $query->get();

            return response()->json([
                'success' => true,
                'data' => $invoices,
                'message' => 'Student invoices retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving student invoices',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get overdue invoices
     */
    public function getOverdueInvoices(Request $request): JsonResponse
    {
        try {
            $query = Invoice::with(['student.user', 'feeStructure'])
                           ->overdue();

            $perPage = $request->get('per_page', 15);
            $invoices = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $invoices,
                'message' => 'Overdue invoices retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving overdue invoices',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate invoices for multiple students from fee structure
     */
    public function generateBulkInvoices(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'fee_structure_id' => 'required|exists:fee_structures,id',
                'student_ids' => 'required|array',
                'student_ids.*' => 'exists:students,id',
                'due_date' => 'nullable|date',
                'description' => 'nullable|string',
            ]);

            DB::beginTransaction();

            $feeStructure = FeeStructure::findOrFail($validated['fee_structure_id']);
            $invoices = [];

            foreach ($validated['student_ids'] as $studentId) {
                // Check if invoice already exists for this student and fee structure
                $existingInvoice = Invoice::where('student_id', $studentId)
                                        ->where('fee_structure_id', $validated['fee_structure_id'])
                                        ->first();

                if (!$existingInvoice) {
                    $invoice = Invoice::create([
                        'student_id' => $studentId,
                        'fee_structure_id' => $validated['fee_structure_id'],
                        'amount_due' => $feeStructure->amount,
                        'due_date' => $validated['due_date'] ?? $feeStructure->due_date,
                        'description' => $validated['description'] ?? $feeStructure->description,
                    ]);

                    $invoice->load(['student.user', 'feeStructure']);
                    $invoices[] = $invoice;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => [
                    'invoices' => $invoices,
                    'created_count' => count($invoices),
                    'total_requested' => count($validated['student_ids'])
                ],
                'message' => count($invoices) . ' invoices created successfully'
            ], 201);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error generating bulk invoices',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
