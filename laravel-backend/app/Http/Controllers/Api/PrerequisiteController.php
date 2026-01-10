<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CoursePrerequisite;
use App\Models\Course;
use App\Models\Student;
use App\Models\Enrollment;
use App\Models\AssignmentGrade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PrerequisiteController extends Controller
{
    /**
     * Get prerequisites for a course
     */
    public function index($courseId)
    {
        try {
            $course = Course::findOrFail($courseId);
            
            $prerequisites = CoursePrerequisite::with(['prerequisiteCourse'])
                ->where('course_id', $courseId)
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'course' => $course,
                    'prerequisites' => $prerequisites
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching prerequisites',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add a prerequisite to a course
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'course_id' => 'required|exists:courses,id',
                'prerequisite_course_id' => 'required|exists:courses,id|different:course_id',
                'minimum_grade' => 'nullable|numeric|min:0|max:5.00',
                'requirement_type' => 'required|in:required,recommended,corequisite'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $prerequisite = CoursePrerequisite::create($request->all());
            $prerequisite->load('prerequisiteCourse');

            return response()->json([
                'success' => true,
                'message' => 'Prerequisite added successfully',
                'data' => $prerequisite
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error adding prerequisite',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update a prerequisite
     */
    public function update(Request $request, $id)
    {
        try {
            $prerequisite = CoursePrerequisite::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'minimum_grade' => 'nullable|numeric|min:0|max:5.00',
                'requirement_type' => 'sometimes|in:required,recommended,corequisite'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $prerequisite->update($request->all());
            $prerequisite->load('prerequisiteCourse');

            return response()->json([
                'success' => true,
                'message' => 'Prerequisite updated successfully',
                'data' => $prerequisite
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating prerequisite',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove a prerequisite
     */
    public function destroy($id)
    {
        try {
            $prerequisite = CoursePrerequisite::findOrFail($id);
            $prerequisite->delete();

            return response()->json([
                'success' => true,
                'message' => 'Prerequisite removed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error removing prerequisite',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if student meets prerequisites for a course
     */
    public function checkEligibility($courseId, $studentId)
    {
        try {
            $course = Course::findOrFail($courseId);
            $student = Student::findOrFail($studentId);

            $prerequisites = CoursePrerequisite::with('prerequisiteCourse')
                ->where('course_id', $courseId)
                ->get();

            if ($prerequisites->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'eligible' => true,
                    'message' => 'No prerequisites required for this course'
                ]);
            }

            $isEligible = true;
            $failedPrerequisites = [];

            foreach ($prerequisites as $prereq) {
                if ($prereq->requirement_type !== 'required') {
                    continue;
                }

                $enrollment = Enrollment::where('student_id', $studentId)
                    ->where('course_id', $prereq->prerequisite_course_id)
                    ->where('status', 'completed')
                    ->first();

                if (!$enrollment) {
                    $isEligible = false;
                    $failedPrerequisites[] = [
                        'course' => $prereq->prerequisiteCourse,
                        'reason' => 'Course not completed'
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'eligible' => $isEligible,
                'message' => $isEligible ? 'Student meets all prerequisites' : 'Prerequisites not met',
                'failed_prerequisites' => $failedPrerequisites
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error checking eligibility',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
