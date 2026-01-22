<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Invoice;
use App\Services\PdfGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    protected $pdfGenerator;

    public function __construct(PdfGeneratorService $pdfGenerator)
    {
        $this->pdfGenerator = $pdfGenerator;
    }

    /**
     * Display a listing of payments
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Payment::with(['invoice.student.user', 'invoice.feeStructure', 'processedBy']);

            // Apply filters
            if ($request->has('invoice_id')) {
                $query->forInvoice($request->invoice_id);
            }

            if ($request->has('payment_method')) {
                $query->byPaymentMethod($request->payment_method);
            }

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('date_from')) {
                $query->where('payment_date', '>=', $request->date_from);
            }

            if ($request->has('date_to')) {
                $query->where('payment_date', '<=', $request->date_to);
            }

            // Sort by payment date by default
            $query->orderBy('payment_date', 'desc');

            // Pagination
            $perPage = $request->get('per_page', 15);
            $payments = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $payments,
                'message' => 'Payments retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving payments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created payment
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'invoice_id' => 'required|exists:invoices,id',
                'amount_paid' => 'required|numeric|min:0.01',
                'payment_method' => 'required|in:cash,bank_transfer,credit_card,debit_card,cheque,online',
                'payment_date' => 'nullable|date',
                'transaction_id' => 'nullable|string|max:255',
                'payment_notes' => 'nullable|string',
            ]);

            DB::beginTransaction();

            // Get the invoice and validate payment amount
            $invoice = Invoice::findOrFail($validated['invoice_id']);

            if ($validated['amount_paid'] > $invoice->balance) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment amount exceeds invoice balance',
                    'data' => [
                        'invoice_balance' => $invoice->balance,
                        'attempted_amount' => $validated['amount_paid']
                    ]
                ], 422);
            }

            // Create the payment
            $payment = Payment::create(array_merge($validated, [
                'processed_by' => Auth::id(),
                'status' => 'completed'
            ]));

            // Update invoice amounts
            $invoice->amount_paid += $validated['amount_paid'];
            $invoice->save(); // This will trigger the model's updating event to recalculate status

            $payment->load(['invoice.student.user', 'invoice.feeStructure', 'processedBy']);

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => $payment,
                'message' => 'Payment recorded successfully'
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
                'message' => 'Error recording payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified payment
     */
    public function show(string $id): JsonResponse
    {
        try {
            $payment = Payment::with(['invoice.student.user', 'invoice.feeStructure', 'processedBy'])
                             ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $payment,
                'message' => 'Payment retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified payment (limited fields)
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $payment = Payment::findOrFail($id);

            // Only allow updating certain fields
            $validated = $request->validate([
                'payment_notes' => 'nullable|string',
                'transaction_id' => 'nullable|string|max:255',
            ]);

            $payment->update($validated);

            $payment->load(['invoice.student.user', 'invoice.feeStructure', 'processedBy']);

            return response()->json([
                'success' => true,
                'data' => $payment,
                'message' => 'Payment updated successfully'
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
                'message' => 'Error updating payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Refund the specified payment
     */
    public function refund(Request $request, string $id): JsonResponse
    {
        try {
            $payment = Payment::findOrFail($id);

            $validated = $request->validate([
                'reason' => 'nullable|string|max:255'
            ]);

            DB::beginTransaction();

            $payment->refund($validated['reason'] ?? 'Refund requested');

            DB::commit();

            $payment->load(['invoice.student.user', 'invoice.feeStructure', 'processedBy']);

            return response()->json([
                'success' => true,
                'data' => $payment,
                'message' => 'Payment refunded successfully'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error refunding payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payments for a specific invoice
     */
    public function getInvoicePayments(string $invoiceId): JsonResponse
    {
        try {
            $payments = Payment::with(['processedBy'])
                              ->forInvoice($invoiceId)
                              ->orderBy('payment_date', 'desc')
                              ->get();

            return response()->json([
                'success' => true,
                'data' => $payments,
                'message' => 'Invoice payments retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving invoice payments',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get payment statistics
     */
    public function getPaymentStats(Request $request): JsonResponse
    {
        try {
            $startDate = $request->get('start_date', now()->startOfMonth());
            $endDate = $request->get('end_date', now()->endOfMonth());

            $stats = [
                'total_payments' => Payment::completed()
                    ->whereBetween('payment_date', [$startDate, $endDate])
                    ->sum('amount_paid'),
                
                'payment_count' => Payment::completed()
                    ->whereBetween('payment_date', [$startDate, $endDate])
                    ->count(),
                
                'payment_methods' => Payment::completed()
                    ->whereBetween('payment_date', [$startDate, $endDate])
                    ->selectRaw('payment_method, COUNT(*) as count, SUM(amount_paid) as total')
                    ->groupBy('payment_method')
                    ->get(),
                
                'daily_payments' => Payment::completed()
                    ->whereBetween('payment_date', [$startDate, $endDate])
                    ->selectRaw('DATE(payment_date) as date, COUNT(*) as count, SUM(amount_paid) as total')
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Payment statistics retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving payment statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate payment receipt PDF
     */
    public function generateReceipt(Request $request, $id)
    {
        try {
            $user = $request->user();
            $student = $user->student;

            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student profile not found'
                ], 404);
            }

            $payment = Payment::with(['invoice.student.user', 'invoice.feeStructure'])
                ->find($id);

            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not found'
                ], 404);
            }

            // Verify the payment belongs to the authenticated student
            if ($payment->invoice->student_id !== $student->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access to payment receipt'
                ], 403);
            }

            $data = [
                'payment' => $payment,
                'invoice' => $payment->invoice,
                'student' => $student->load('user', 'department'),
                'generated_date' => now()->format('F d, Y'),
                'receipt_number' => 'RCP-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT),
            ];

            $pdf = $this->pdfGenerator->generatePaymentReceipt($data);

            return response($pdf, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="payment-receipt-' . $payment->id . '.pdf"');

        } catch (\Exception $e) {
            Log::error('Payment receipt generation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate payment receipt'
            ], 500);
        }
    }
}
