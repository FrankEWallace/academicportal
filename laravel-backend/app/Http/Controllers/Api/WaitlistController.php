<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CourseWaitlist;
use App\Models\Course;
use App\Models\Student;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class WaitlistController extends Controller
{
    /**
     * Get waitlist for a course
     */
    public function index($courseId)
    {
        try {
            $course = Course::findOrFail($courseId);
            
            $waitlist = CourseWaitlist::with(['student', 'course'])
                ->where('course_id', $courseId)
                ->where('status', 'waiting')
                ->orderBy('position')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'course' => $course,
                    'waitlist' => $waitlist,
                    'count' => $waitlist->count()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching waitlist',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add student to waitlist
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'course_id' => 'required|exists:courses,id',
                'student_id' => 'required|exists:students,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Check if already enrolled
            $existingEnrollment = Enrollment::where('course_id', $request->course_id)
                ->where('student_id', $request->student_id)
                ->whereIn('status', ['enrolled', 'completed'])
                ->first();

            if ($existingEnrollment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student is already enrolled in this course'
                ], 422);
            }

            // Check if already on waitlist
            $existingWaitlist = CourseWaitlist::where('course_id', $request->course_id)
                ->where('student_id', $request->student_id)
                ->where('status', 'waiting')
                ->first();

            if ($existingWaitlist) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student is already on the waitlist',
                    'data' => $existingWaitlist
                ], 422);
            }

            // Get next position
            $maxPosition = CourseWaitlist::where('course_id', $request->course_id)
                ->where('status', 'waiting')
                ->max('position');

            $waitlistEntry = CourseWaitlist::create([
                'course_id' => $request->course_id,
                'student_id' => $request->student_id,
                'position' => $maxPosition ? $maxPosition + 1 : 1,
                'status' => 'waiting'
            ]);

            $waitlistEntry->load(['student', 'course']);

            return response()->json([
                'success' => true,
                'message' => 'Added to waitlist successfully',
                'data' => $waitlistEntry
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error adding to waitlist',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get student's waitlist entries
     */
    public function studentWaitlist($studentId)
    {
        try {
            $student = Student::findOrFail($studentId);

            $waitlist = CourseWaitlist::with('course')
                ->where('student_id', $studentId)
                ->where('status', 'waiting')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $waitlist
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching student waitlist',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove student from waitlist
     */
    public function destroy($id)
    {
        try {
            $waitlistEntry = CourseWaitlist::findOrFail($id);
            $courseId = $waitlistEntry->course_id;
            $position = $waitlistEntry->position;

            $waitlistEntry->delete();

            // Reorder remaining waitlist entries
            CourseWaitlist::where('course_id', $courseId)
                ->where('status', 'waiting')
                ->where('position', '>', $position)
                ->decrement('position');

            return response()->json([
                'success' => true,
                'message' => 'Removed from waitlist successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error removing from waitlist',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process waitlist when spot becomes available
     */
    public function processWaitlist($courseId)
    {
        try {
            $course = Course::findOrFail($courseId);

            // Get current enrollment count
            $enrolledCount = Enrollment::where('course_id', $courseId)
                ->where('status', 'enrolled')
                ->count();

            if ($enrolledCount >= $course->capacity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Course is at full capacity'
                ], 422);
            }

            $availableSpots = $course->capacity - $enrolledCount;

            // Get waitlist entries in order
            $waitlistEntries = CourseWaitlist::with('student')
                ->where('course_id', $courseId)
                ->where('status', 'waiting')
                ->orderBy('position')
                ->limit($availableSpots)
                ->get();

            $enrolled = [];

            DB::beginTransaction();
            try {
                foreach ($waitlistEntries as $entry) {
                    // Create enrollment
                    $enrollment = Enrollment::create([
                        'student_id' => $entry->student_id,
                        'course_id' => $courseId,
                        'status' => 'enrolled',
                        'enrolled_at' => now()
                    ]);

                    // Update waitlist entry
                    $entry->update([
                        'status' => 'enrolled',
                        'enrolled_at' => now()
                    ]);

                    $enrolled[] = [
                        'student' => $entry->student,
                        'enrollment' => $enrollment
                    ];
                }

                // Reorder remaining waitlist
                $this->reorderWaitlist($courseId);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Waitlist processed successfully',
                    'data' => [
                        'enrolled_count' => count($enrolled),
                        'enrolled_students' => $enrolled
                    ]
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing waitlist',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reorder waitlist positions
     */
    private function reorderWaitlist($courseId)
    {
        $waitlist = CourseWaitlist::where('course_id', $courseId)
            ->where('status', 'waiting')
            ->orderBy('position')
            ->get();

        $position = 1;
        foreach ($waitlist as $entry) {
            $entry->update(['position' => $position++]);
        }
    }
}
