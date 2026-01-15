<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\EnrollmentConfirmation;
use App\Models\EnrollmentConfirmationCourse;
use App\Models\Enrollment;
use App\Models\Course;
use App\Models\Timetable;
use App\Models\CoursePrerequisite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class EnrollmentConfirmationController extends Controller
{
    /**
     * Get enrollment summary for current semester.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEnrollmentSummary()
    {
        $student = Auth::user()->student;
        
        if (!$student) {
            return response()->json(['message' => 'Student profile not found'], 404);
        }

        $currentSemester = $this->getCurrentSemesterCode();

        // Get student's enrolled courses for current semester
        $enrollments = Enrollment::where('student_id', $student->id)
            ->where('semester', $currentSemester)
            ->with(['course'])
            ->get();

        if ($enrollments->isEmpty()) {
            return response()->json([
                'message' => 'No courses enrolled for current semester',
                'semester_code' => $currentSemester,
                'total_courses' => 0,
                'total_units' => 0,
            ]);
        }

        $totalUnits = $enrollments->sum(function ($enrollment) {
            return $enrollment->course->credits ?? 0;
        });

        // Check if confirmation already exists
        $confirmation = EnrollmentConfirmation::where('student_id', $student->id)
            ->where('semester_code', $currentSemester)
            ->with(['courses'])
            ->first();

        return response()->json([
            'semester_code' => $currentSemester,
            'enrollments' => $enrollments,
            'total_courses' => $enrollments->count(),
            'total_units' => $totalUnits,
            'confirmation' => $confirmation,
            'is_confirmed' => $confirmation && $confirmation->confirmed,
        ]);
    }

    /**
     * Validate enrollment (check prerequisites and schedule conflicts).
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function validateEnrollment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'semester_code' => 'required|string|max:20',
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

        // Get enrolled courses
        $enrollments = Enrollment::where('student_id', $student->id)
            ->where('semester', $request->semester_code)
            ->with(['course'])
            ->get();

        $validationResults = [];
        $allPrerequisitesSatisfied = true;
        $allConflictsResolved = true;

        foreach ($enrollments as $enrollment) {
            $course = $enrollment->course;
            
            // Check prerequisites
            $prerequisitesMet = $this->checkPrerequisites($student->id, $course->id);
            
            // Check schedule conflicts
            $conflicts = $this->checkScheduleConflicts($student->id, $course->id, $request->semester_code);
            
            $validationResults[] = [
                'course_id' => $course->id,
                'course_code' => $course->course_code,
                'course_title' => $course->course_name,
                'units' => $course->credits,
                'prerequisites_met' => $prerequisitesMet,
                'has_schedule_conflict' => !empty($conflicts),
                'conflict_details' => $conflicts,
            ];

            if (!$prerequisitesMet) {
                $allPrerequisitesSatisfied = false;
            }
            if (!empty($conflicts)) {
                $allConflictsResolved = false;
            }
        }

        // Create or update enrollment confirmation record
        $confirmation = EnrollmentConfirmation::updateOrCreate(
            [
                'student_id' => $student->id,
                'semester_code' => $request->semester_code,
            ],
            [
                'academic_year' => $this->getAcademicYear($request->semester_code),
                'total_courses' => $enrollments->count(),
                'total_units' => $enrollments->sum(fn($e) => $e->course->credits ?? 0),
                'prerequisites_satisfied' => $allPrerequisitesSatisfied,
                'schedule_conflicts_resolved' => $allConflictsResolved,
            ]
        );

        // Save individual course validations
        foreach ($validationResults as $result) {
            EnrollmentConfirmationCourse::updateOrCreate(
                [
                    'enrollment_confirmation_id' => $confirmation->id,
                    'course_id' => $result['course_id'],
                ],
                [
                    'course_code' => $result['course_code'],
                    'course_title' => $result['course_title'],
                    'units' => $result['units'],
                    'prerequisites_met' => $result['prerequisites_met'],
                    'has_schedule_conflict' => $result['has_schedule_conflict'],
                    'conflict_details' => !empty($result['conflict_details']) ? json_encode($result['conflict_details']) : null,
                ]
            );
        }

        return response()->json([
            'validation_results' => $validationResults,
            'summary' => [
                'all_prerequisites_satisfied' => $allPrerequisitesSatisfied,
                'all_conflicts_resolved' => $allConflictsResolved,
                'can_confirm' => $allPrerequisitesSatisfied && $allConflictsResolved,
            ],
            'confirmation_id' => $confirmation->id,
        ]);
    }

    /**
     * Confirm enrollment.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function confirmEnrollment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'confirmation_id' => 'required|exists:enrollment_confirmations,id',
            'timetable_understood' => 'required|boolean|accepted',
            'attendance_policy_agreed' => 'required|boolean|accepted',
            'academic_calendar_checked' => 'required|boolean|accepted',
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

        $confirmation = EnrollmentConfirmation::findOrFail($request->confirmation_id);

        // Verify ownership
        if ($confirmation->student_id !== $student->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Check if all validations passed
        if (!$confirmation->allConfirmed()) {
            return response()->json([
                'message' => 'Cannot confirm enrollment. Please ensure all checkboxes are checked.',
            ], 400);
        }

        // Update confirmation
        $confirmation->update([
            'timetable_understood' => $request->timetable_understood,
            'attendance_policy_agreed' => $request->attendance_policy_agreed,
            'academic_calendar_checked' => $request->academic_calendar_checked,
            'confirmed' => true,
            'confirmation_date' => now(),
        ]);

        // Send confirmation email
        $this->sendConfirmationEmail($student, $confirmation);

        $confirmation->update([
            'confirmation_email_sent' => Auth::user()->email,
        ]);

        return response()->json([
            'message' => 'Enrollment confirmed successfully',
            'confirmation' => $confirmation->fresh(['courses']),
        ]);
    }

    /**
     * Get confirmation email details.
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getConfirmationEmail($id)
    {
        $student = Auth::user()->student;
        
        if (!$student) {
            return response()->json(['message' => 'Student profile not found'], 404);
        }

        $confirmation = EnrollmentConfirmation::where('id', $id)
            ->where('student_id', $student->id)
            ->with(['courses'])
            ->firstOrFail();

        return response()->json([
            'confirmation' => $confirmation,
            'email_sent_to' => $confirmation->confirmation_email_sent,
            'confirmed_at' => $confirmation->confirmation_date,
        ]);
    }

    /**
     * Helper: Check if prerequisites are met.
     */
    private function checkPrerequisites(int $studentId, int $courseId): bool
    {
        $prerequisites = CoursePrerequisite::where('course_id', $courseId)->get();
        
        if ($prerequisites->isEmpty()) {
            return true; // No prerequisites required
        }

        // Check if student has passed all prerequisite courses
        foreach ($prerequisites as $prereq) {
            $passed = Enrollment::where('student_id', $studentId)
                ->where('course_id', $prereq->prerequisite_course_id)
                ->where('status', 'passed') // Assuming you have this status
                ->exists();

            if (!$passed) {
                return false;
            }
        }

        return true;
    }

    /**
     * Helper: Check for schedule conflicts.
     */
    private function checkScheduleConflicts(int $studentId, int $courseId, string $semesterCode): array
    {
        // Get timetable for this course
        $courseTimetable = Timetable::where('course_id', $courseId)
            ->where('semester', $semesterCode)
            ->get();

        if ($courseTimetable->isEmpty()) {
            return []; // No timetable, no conflicts
        }

        // Get all enrolled courses for student
        $studentEnrollments = Enrollment::where('student_id', $studentId)
            ->where('semester', $semesterCode)
            ->where('course_id', '!=', $courseId)
            ->pluck('course_id');

        // Check for time conflicts
        $conflicts = [];
        foreach ($courseTimetable as $slot) {
            $conflictingSlots = Timetable::whereIn('course_id', $studentEnrollments)
                ->where('semester', $semesterCode)
                ->where('day_of_week', $slot->day_of_week)
                ->where(function ($query) use ($slot) {
                    $query->whereBetween('start_time', [$slot->start_time, $slot->end_time])
                        ->orWhereBetween('end_time', [$slot->start_time, $slot->end_time])
                        ->orWhere(function ($q) use ($slot) {
                            $q->where('start_time', '<=', $slot->start_time)
                              ->where('end_time', '>=', $slot->end_time);
                        });
                })
                ->with(['course'])
                ->get();

            foreach ($conflictingSlots as $conflict) {
                $conflicts[] = [
                    'day' => $slot->day_of_week,
                    'time' => "{$slot->start_time} - {$slot->end_time}",
                    'conflicting_course' => $conflict->course->course_code ?? 'Unknown',
                ];
            }
        }

        return $conflicts;
    }

    /**
     * Helper: Send confirmation email.
     */
    private function sendConfirmationEmail($student, $confirmation)
    {
        // TODO: Implement email sending
        // Mail::to($student->email)->send(new EnrollmentConfirmed($confirmation));
    }

    /**
     * Helper: Get current semester code.
     */
    private function getCurrentSemesterCode(): string
    {
        return '2025-2';
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
