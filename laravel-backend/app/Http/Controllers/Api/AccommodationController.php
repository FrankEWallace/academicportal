<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StudentAccommodation;
use App\Models\AccommodationRoommate;
use App\Models\AccommodationFee;
use App\Models\AccommodationAmenity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

class AccommodationController extends Controller
{
    /**
     * Get current accommodation details.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCurrentAccommodation()
    {
        $student = Auth::user()->student;
        
        if (!$student) {
            return response()->json(['message' => 'Student profile not found'], 404);
        }

        $currentAcademicYear = $this->getCurrentAcademicYear();

        $accommodation = StudentAccommodation::where('student_id', $student->id)
            ->where('academic_year', $currentAcademicYear)
            ->first();

        if (!$accommodation) {
            return response()->json([
                'message' => 'No accommodation allocated for current academic year',
                'academic_year' => $currentAcademicYear,
                'has_accommodation' => false,
            ]);
        }

        return response()->json([
            'accommodation' => [
                'id' => $accommodation->id,
                'hostel_name' => $accommodation->hostel_name,
                'block' => $accommodation->block,
                'floor' => $accommodation->floor,
                'room_number' => $accommodation->room_number,
                'room_type' => $accommodation->room_type,
                'bed_number' => $accommodation->bed_number,
                'full_room' => $accommodation->full_room,
                'status' => $accommodation->status,
                'allocation_date' => $accommodation->allocation_date,
                'expiry_date' => $accommodation->expiry_date,
                'renewal_eligible' => $accommodation->renewal_eligible,
                'renewal_deadline' => $accommodation->renewal_deadline,
                'renewal_due_soon' => $accommodation->renewalDueSoon(),
                'is_active' => $accommodation->isActive(),
                'allocation_letter_url' => $accommodation->allocation_letter_path 
                    ? Storage::url($accommodation->allocation_letter_path) 
                    : null,
            ],
            'academic_year' => $currentAcademicYear,
        ]);
    }

    /**
     * Get roommates information.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRoommates()
    {
        $student = Auth::user()->student;
        
        if (!$student) {
            return response()->json(['message' => 'Student profile not found'], 404);
        }

        $currentAcademicYear = $this->getCurrentAcademicYear();

        $accommodation = StudentAccommodation::where('student_id', $student->id)
            ->where('academic_year', $currentAcademicYear)
            ->first();

        if (!$accommodation) {
            return response()->json([
                'message' => 'No accommodation found',
                'roommates' => [],
            ]);
        }

        $roommates = AccommodationRoommate::where('accommodation_id', $accommodation->id)
            ->where('is_active', true)
            ->get();

        return response()->json([
            'room_info' => [
                'hostel' => $accommodation->hostel_name,
                'room' => $accommodation->room_number,
                'room_type' => $accommodation->room_type,
            ],
            'roommates' => $roommates->map(function ($roommate) {
                return [
                    'id' => $roommate->id,
                    'name' => $roommate->roommate_name,
                    'matric_no' => $roommate->roommate_matric_no,
                    'department' => $roommate->roommate_department,
                    'level' => $roommate->roommate_level,
                    'phone' => $roommate->roommate_phone,
                    'email' => $roommate->roommate_email,
                ];
            }),
            'total_roommates' => $roommates->count(),
        ]);
    }

    /**
     * Get accommodation fees.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAccommodationFees()
    {
        $student = Auth::user()->student;
        
        if (!$student) {
            return response()->json(['message' => 'Student profile not found'], 404);
        }

        $currentAcademicYear = $this->getCurrentAcademicYear();

        $accommodation = StudentAccommodation::where('student_id', $student->id)
            ->where('academic_year', $currentAcademicYear)
            ->first();

        if (!$accommodation) {
            return response()->json([
                'message' => 'No accommodation found',
                'fees' => [],
            ]);
        }

        $fees = AccommodationFee::where('accommodation_id', $accommodation->id)
            ->orderBy('due_date')
            ->get();

        $totalAmount = $fees->sum('amount');
        $totalPaid = $fees->sum('amount_paid');
        $totalBalance = $fees->sum('balance');

        return response()->json([
            'fees' => $fees->map(function ($fee) {
                return [
                    'id' => $fee->id,
                    'fee_type' => $fee->fee_type,
                    'amount' => $fee->amount,
                    'amount_paid' => $fee->amount_paid,
                    'balance' => $fee->balance,
                    'status' => $fee->status,
                    'due_date' => $fee->due_date,
                    'payment_date' => $fee->payment_date,
                    'receipt_number' => $fee->receipt_number,
                    'payment_percentage' => $fee->paymentPercentage(),
                    'is_overdue' => $fee->isOverdue(),
                ];
            }),
            'summary' => [
                'total_amount' => $totalAmount,
                'total_paid' => $totalPaid,
                'total_balance' => $totalBalance,
                'payment_percentage' => $totalAmount > 0 ? round(($totalPaid / $totalAmount) * 100, 2) : 0,
            ],
        ]);
    }

    /**
     * Get hostel amenities.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHostelAmenities()
    {
        $student = Auth::user()->student;
        
        if (!$student) {
            return response()->json(['message' => 'Student profile not found'], 404);
        }

        $currentAcademicYear = $this->getCurrentAcademicYear();

        $accommodation = StudentAccommodation::where('student_id', $student->id)
            ->where('academic_year', $currentAcademicYear)
            ->first();

        if (!$accommodation) {
            return response()->json([
                'message' => 'No accommodation found',
                'amenities' => [],
            ]);
        }

        $amenities = AccommodationAmenity::byHostel($accommodation->hostel_name)
            ->available()
            ->get();

        return response()->json([
            'hostel_name' => $accommodation->hostel_name,
            'amenities' => $amenities->map(function ($amenity) {
                return [
                    'name' => $amenity->amenity_name,
                    'icon' => $amenity->icon,
                    'description' => $amenity->description,
                    'is_available' => $amenity->is_available,
                ];
            }),
            'total_amenities' => $amenities->count(),
        ]);
    }

    /**
     * Download allocation letter PDF.
     * 
     * @return \Illuminate\Http\Response
     */
    public function downloadAllocationLetter()
    {
        $student = Auth::user()->student;
        
        if (!$student) {
            return response()->json(['message' => 'Student profile not found'], 404);
        }

        $currentAcademicYear = $this->getCurrentAcademicYear();

        $accommodation = StudentAccommodation::where('student_id', $student->id)
            ->where('academic_year', $currentAcademicYear)
            ->firstOrFail();

        // Check if allocation letter exists
        if ($accommodation->allocation_letter_path && Storage::disk('public')->exists($accommodation->allocation_letter_path)) {
            return Storage::disk('public')->download($accommodation->allocation_letter_path);
        }

        // Generate new allocation letter
        $pdf = Pdf::loadView('accommodation.allocation-letter', [
            'student' => $student,
            'accommodation' => $accommodation,
            'generated_date' => now(),
        ]);
        
        // Save the PDF
        $filename = "allocation_letter_{$student->matric_no}_{$currentAcademicYear}.pdf";
        $path = "allocation_letters/{$filename}";
        Storage::disk('public')->put($path, $pdf->output());
        
        // Update accommodation record
        $accommodation->update(['allocation_letter_path' => $path]);
        
        return $pdf->download($filename);
    }

    /**
     * Helper: Get current academic year.
     */
    private function getCurrentAcademicYear(): string
    {
        // For now, return a default. In production, get from settings
        return '2025/2026';
    }
}
