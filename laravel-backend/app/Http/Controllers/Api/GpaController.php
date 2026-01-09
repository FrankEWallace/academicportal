<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GpaCalculationService;
use App\Models\Student;
use App\Models\GradePoint;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GpaController extends Controller
{
    protected $gpaService;

    public function __construct(GpaCalculationService $gpaService)
    {
        $this->gpaService = $gpaService;
    }

    /**
     * Get student's cumulative GPA
     * GET /api/students/{studentId}/gpa
     */
    public function getStudentGpa(int $studentId): JsonResponse
    {
        try {
            $student = Student::with('user')->find($studentId);
            
            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student not found'
                ], 404);
            }

            $gpaData = $this->gpaService->calculateCumulativeGpa($studentId);

            // Update student record with latest CGPA
            $this->gpaService->updateStudentCgpa($studentId);

            return response()->json([
                'success' => true,
                'data' => [
                    'student' => [
                        'id' => $student->id,
                        'name' => $student->user->name ?? 'Unknown',
                        'student_id' => $student->student_id,
                    ],
                    'gpa_details' => $gpaData
                ],
                'message' => 'GPA calculated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error calculating GPA: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get semester GPA for a student
     * GET /api/students/{studentId}/gpa/semester/{semester}
     */
    public function getSemesterGpa(int $studentId, int $semester): JsonResponse
    {
        try {
            $student = Student::with('user')->find($studentId);
            
            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student not found'
                ], 404);
            }

            $semesterGpa = $this->gpaService->calculateSemesterGpa($studentId, $semester);

            return response()->json([
                'success' => true,
                'data' => [
                    'student' => [
                        'id' => $student->id,
                        'name' => $student->user->name ?? 'Unknown',
                        'student_id' => $student->student_id,
                    ],
                    'semester_gpa' => $semesterGpa
                ],
                'message' => 'Semester GPA calculated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error calculating semester GPA: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get course grade for a student
     * GET /api/students/{studentId}/courses/{courseId}/grade
     */
    public function getCourseGrade(int $studentId, int $courseId): JsonResponse
    {
        try {
            $courseGrade = $this->gpaService->calculateCourseGrade($studentId, $courseId);

            return response()->json([
                'success' => true,
                'data' => $courseGrade,
                'message' => 'Course grade calculated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error calculating course grade: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get GPA statistics for a course
     * GET /api/courses/{courseId}/gpa-statistics
     */
    public function getCourseStatistics(int $courseId): JsonResponse
    {
        try {
            $statistics = $this->gpaService->getCourseGpaStatistics($courseId);

            return response()->json([
                'success' => true,
                'data' => $statistics,
                'message' => 'Course statistics retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving course statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get batch GPA for multiple students (for rankings)
     * POST /api/students/gpa/batch
     */
    public function getBatchGpa(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'student_ids' => 'required|array',
                'student_ids.*' => 'required|integer|exists:students,id'
            ]);

            $batchGpa = $this->gpaService->calculateBatchGpa($request->student_ids);

            return response()->json([
                'success' => true,
                'data' => $batchGpa,
                'message' => 'Batch GPA calculated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error calculating batch GPA: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all grade points (letter grades and their values)
     * GET /api/grade-points
     */
    public function getGradePoints(): JsonResponse
    {
        try {
            $gradePoints = GradePoint::orderBy('order')->get();

            return response()->json([
                'success' => true,
                'data' => $gradePoints,
                'message' => 'Grade points retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving grade points: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get letter grade for a percentage
     * POST /api/grade-points/calculate
     */
    public function calculateLetterGrade(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'percentage' => 'required|numeric|min:0|max:100'
            ]);

            $gradeInfo = $this->gpaService->getLetterGrade($request->percentage);

            return response()->json([
                'success' => true,
                'data' => [
                    'percentage' => $request->percentage,
                    'grade_info' => $gradeInfo
                ],
                'message' => 'Letter grade calculated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error calculating letter grade: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update CGPA for a student
     * PUT /api/students/{studentId}/gpa/update
     */
    public function updateStudentGpa(int $studentId): JsonResponse
    {
        try {
            $updated = $this->gpaService->updateStudentCgpa($studentId);

            if ($updated) {
                $student = Student::with('user')->find($studentId);
                
                return response()->json([
                    'success' => true,
                    'data' => [
                        'student_id' => $student->id,
                        'cgpa' => $student->current_gpa,
                        'total_credits' => $student->total_credits
                    ],
                    'message' => 'Student CGPA updated successfully'
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to update student CGPA'
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating student CGPA: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get class rankings based on CGPA
     * GET /api/students/rankings
     */
    public function getClassRankings(Request $request): JsonResponse
    {
        try {
            // Get all active students
            $students = Student::where('status', 'enrolled')
                ->pluck('id')
                ->toArray();

            if (empty($students)) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'No enrolled students found'
                ]);
            }

            $rankings = $this->gpaService->calculateBatchGpa($students);

            return response()->json([
                'success' => true,
                'data' => $rankings,
                'message' => 'Class rankings retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving class rankings: ' . $e->getMessage()
            ], 500);
        }
    }
}
