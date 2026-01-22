<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use App\Models\Enrollment;
use App\Services\PdfGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class StudentDocumentController extends Controller
{
    protected $pdfGenerator;

    public function __construct(PdfGeneratorService $pdfGenerator)
    {
        $this->pdfGenerator = $pdfGenerator;
    }

    /**
     * Generate and download admission letter
     */
    public function admissionLetter(Request $request)
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

            $data = [
                'student' => $student->load('user', 'department', 'degree_program'),
                'date' => now()->format('F d, Y'),
                'academic_year' => now()->year,
            ];

            $pdf = $this->pdfGenerator->generateAdmissionLetter($data);

            return response($pdf, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="admission-letter-' . $student->student_id . '.pdf"');

        } catch (\Exception $e) {
            Log::error('Admission letter generation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate admission letter'
            ], 500);
        }
    }

    /**
     * Generate and download student ID card
     */
    public function idCard(Request $request)
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

            $data = [
                'student' => $student->load('user', 'department', 'degree_program'),
                'photo_url' => $student->photo_url ?? asset('images/default-avatar.png'),
                'qr_code' => $this->generateQRCode($student->student_id),
                'issue_date' => now()->format('Y-m-d'),
                'expiry_date' => now()->addYear()->format('Y-m-d'),
            ];

            $pdf = $this->pdfGenerator->generateIDCard($data);

            return response($pdf, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="student-id-' . $student->student_id . '.pdf"');

        } catch (\Exception $e) {
            Log::error('ID card generation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate ID card'
            ], 500);
        }
    }

    /**
     * Generate and download course registration form
     */
    public function courseRegistrationForm(Request $request)
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

            // Get current semester or specified semester
            $semesterCode = $request->query('semester');
            
            $enrollments = Enrollment::where('student_id', $student->id)
                ->where('status', 'enrolled')
                ->with(['course.teacher.user', 'course.department'])
                ->when($semesterCode, function($query) use ($semesterCode) {
                    $query->where('semester_code', $semesterCode);
                })
                ->get();

            if ($enrollments->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active course enrollments found'
                ], 404);
            }

            $data = [
                'student' => $student->load('user', 'department', 'degree_program'),
                'enrollments' => $enrollments,
                'semester' => $semesterCode ?? 'Current Semester',
                'total_credits' => $enrollments->sum(function($enrollment) {
                    return $enrollment->course->credits ?? 0;
                }),
                'registration_date' => now()->format('F d, Y'),
            ];

            $pdf = $this->pdfGenerator->generateCourseRegistration($data);

            return response($pdf, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="course-registration-' . $student->student_id . '.pdf"');

        } catch (\Exception $e) {
            Log::error('Course registration form generation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate course registration form'
            ], 500);
        }
    }

    /**
     * Generate and download timetable
     */
    public function timetable(Request $request)
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

            // Get student's courses
            $courseIds = Enrollment::where('student_id', $student->id)
                ->where('status', 'enrolled')
                ->pluck('course_id');

            if ($courseIds->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active course enrollments found'
                ], 404);
            }

            // Get timetable entries for student's courses
            $timetable = \App\Models\Timetable::whereIn('course_id', $courseIds)
                ->with(['course', 'teacher.user'])
                ->orderBy('day_of_week')
                ->orderBy('start_time')
                ->get()
                ->groupBy('day_of_week');

            $data = [
                'student' => $student->load('user', 'department'),
                'timetable' => $timetable,
                'semester' => 'Current Semester',
                'generated_date' => now()->format('F d, Y'),
            ];

            $pdf = $this->pdfGenerator->generateTimetable($data);

            return response($pdf, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="timetable-' . $student->student_id . '.pdf"');

        } catch (\Exception $e) {
            Log::error('Timetable generation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate timetable'
            ], 500);
        }
    }

    /**
     * Generate and download exam timetable
     */
    public function examTimetable(Request $request)
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

            // Get student's courses
            $courseIds = Enrollment::where('student_id', $student->id)
                ->where('status', 'enrolled')
                ->pluck('course_id');

            // This will need an Exam model - for now, return placeholder
            $data = [
                'student' => $student->load('user', 'department'),
                'exams' => [], // TODO: Implement when Exam model is ready
                'semester' => 'Current Semester',
                'generated_date' => now()->format('F d, Y'),
            ];

            $pdf = $this->pdfGenerator->generateExamTimetable($data);

            return response($pdf, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="exam-timetable-' . $student->student_id . '.pdf"');

        } catch (\Exception $e) {
            Log::error('Exam timetable generation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate exam timetable'
            ], 500);
        }
    }

    /**
     * Generate QR code for student ID
     */
    private function generateQRCode(string $studentId): string
    {
        // Simple QR code data - can be enhanced with QR code library
        $data = [
            'student_id' => $studentId,
            'institution' => 'Academic Nexus University',
            'verified' => true,
            'timestamp' => now()->timestamp,
        ];

        return base64_encode(json_encode($data));
    }
}
