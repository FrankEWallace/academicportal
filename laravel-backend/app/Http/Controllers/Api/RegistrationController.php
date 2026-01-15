<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Models\StudentInsurance;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Barryvdh\DomPDF\Facade\Pdf;

class RegistrationController extends Controller
{
    /**
     * Get current semester registration status.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCurrentRegistration()
    {
        $student = Auth::user()->student;
        
        if (!$student) {
            return response()->json(['message' => 'Student profile not found'], 404);
        }

        // Get current semester (you might want to get this from a settings table)
        $currentSemester = $this->getCurrentSemesterCode();
        
        $registration = Registration::where('student_id', $student->id)
            ->where('semester_code', $currentSemester)
            ->with(['verifiedBy:id,name'])
            ->first();

        if (!$registration) {
            return response()->json([
                'message' => 'No registration found for current semester',
                'semester_code' => $currentSemester,
            ], 404);
        }

        return response()->json([
            'registration' => $registration,
            'payment_percentage' => $registration->paymentPercentage(),
            'fully_verified' => $registration->isFullyVerified(),
        ]);
    }

    /**
     * Get registration history.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRegistrationHistory()
    {
        $student = Auth::user()->student;
        
        if (!$student) {
            return response()->json(['message' => 'Student profile not found'], 404);
        }

        $registrations = Registration::where('student_id', $student->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($reg) {
                return [
                    'id' => $reg->id,
                    'semester_code' => $reg->semester_code,
                    'academic_year' => $reg->academic_year,
                    'status' => $reg->status,
                    'total_fees' => $reg->total_fees,
                    'amount_paid' => $reg->amount_paid,
                    'balance' => $reg->balance,
                    'fees_verified' => $reg->fees_verified,
                    'insurance_verified' => $reg->insurance_verified,
                    'registration_date' => $reg->registration_date,
                    'payment_percentage' => $reg->paymentPercentage(),
                ];
            });

        return response()->json([
            'registrations' => $registrations,
            'total_count' => $registrations->count(),
        ]);
    }

    /**
     * Upload insurance document.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadInsurance(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'semester_code' => 'required|string|max:20',
            'provider' => 'required|in:nhis,private,other',
            'policy_number' => 'nullable|string|max:100',
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
            'expiry_date' => 'required|date|after:today',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $student = Auth::user()->student;
        
        if (!$student) {
            return response()->json(['message' => 'Student profile not found'], 404);
        }

        // Store the document
        $file = $request->file('document');
        $filename = 'insurance_' . $student->id . '_' . time() . '.' . $file->extension();
        $path = $file->storeAs('insurance_documents', $filename, 'public');

        // Create or update insurance record
        $insurance = StudentInsurance::updateOrCreate(
            [
                'student_id' => $student->id,
                'semester_code' => $request->semester_code,
            ],
            [
                'academic_year' => $this->getAcademicYear($request->semester_code),
                'provider' => $request->provider,
                'policy_number' => $request->policy_number,
                'document_path' => $path,
                'expiry_date' => $request->expiry_date,
                'status' => 'pending',
                'submission_date' => now(),
            ]
        );

        return response()->json([
            'message' => 'Insurance document uploaded successfully',
            'insurance' => $insurance,
        ], 201);
    }

    /**
     * Get insurance status.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInsuranceStatus()
    {
        $student = Auth::user()->student;
        
        if (!$student) {
            return response()->json(['message' => 'Student profile not found'], 404);
        }

        $currentSemester = $this->getCurrentSemesterCode();
        
        $insurance = StudentInsurance::where('student_id', $student->id)
            ->where('semester_code', $currentSemester)
            ->with(['verifiedBy:id,name'])
            ->first();

        if (!$insurance) {
            return response()->json([
                'message' => 'No insurance record found for current semester',
                'semester_code' => $currentSemester,
                'has_insurance' => false,
            ]);
        }

        return response()->json([
            'insurance' => $insurance,
            'is_valid' => $insurance->isValid(),
            'is_expired' => $insurance->isExpired(),
            'document_url' => $insurance->document_path ? Storage::url($insurance->document_path) : null,
        ]);
    }

    /**
     * Get invoices for student.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInvoices()
    {
        $student = Auth::user()->student;
        
        if (!$student) {
            return response()->json(['message' => 'Student profile not found'], 404);
        }

        $invoices = Invoice::where('student_id', $student->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'invoices' => $invoices,
            'total_count' => $invoices->count(),
        ]);
    }

    /**
     * Download invoice PDF.
     * 
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function downloadInvoice($id)
    {
        $student = Auth::user()->student;
        
        if (!$student) {
            return response()->json(['message' => 'Student profile not found'], 404);
        }

        $invoice = Invoice::where('id', $id)
            ->where('student_id', $student->id)
            ->firstOrFail();

        // Generate PDF
        $pdf = Pdf::loadView('invoices.pdf', ['invoice' => $invoice]);
        
        return $pdf->download("invoice_{$invoice->invoice_number}.pdf");
    }

    /**
     * Get payment history.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPaymentHistory()
    {
        $student = Auth::user()->student;
        
        if (!$student) {
            return response()->json(['message' => 'Student profile not found'], 404);
        }

        $payments = Payment::where('student_id', $student->id)
            ->orderBy('payment_date', 'desc')
            ->get();

        return response()->json([
            'payments' => $payments,
            'total_paid' => $payments->sum('amount'),
            'total_count' => $payments->count(),
        ]);
    }

    /**
     * Verify payment (for admin use, but can be triggered by student to check).
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reference' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $student = Auth::user()->student;
        
        if (!$student) {
            return response()->json(['message' => 'Student profile not found'], 404);
        }

        // Check if payment exists with this reference
        $payment = Payment::where('student_id', $student->id)
            ->where('reference', $request->reference)
            ->first();

        if (!$payment) {
            return response()->json([
                'verified' => false,
                'message' => 'Payment not found with this reference',
            ], 404);
        }

        return response()->json([
            'verified' => true,
            'payment' => $payment,
            'message' => 'Payment verified successfully',
        ]);
    }

    /**
     * Helper: Get current semester code.
     * TODO: This should be retrieved from a system settings table
     */
    private function getCurrentSemesterCode(): string
    {
        // For now, return a default. In production, get from settings
        return '2025-2'; // Second semester of 2025
    }

    /**
     * Helper: Get academic year from semester code.
     */
    private function getAcademicYear(string $semesterCode): string
    {
        $year = substr($semesterCode, 0, 4);
        $nextYear = ((int)$year) + 1;
        return "{$year}/{$nextYear}";
    }
}
