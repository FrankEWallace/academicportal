<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\DegreeProgram;
use App\Models\ProgramRequirement;
use App\Models\Enrollment;
use App\Models\AssignmentGrade;
use Illuminate\Http\Request;

class DegreeProgressController extends Controller
{
    /**
     * Get student's degree progress
     */
    public function show($studentId)
    {
        try {
            $student = Student::findOrFail($studentId);
            
            if (!$student->degree_program_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student is not enrolled in a degree program'
                ], 404);
            }

            $program = DegreeProgram::findOrFail($student->degree_program_id);
            
            // Get completed courses
            $completedEnrollments = Enrollment::with('course')
                ->where('student_id', $studentId)
                ->where('status', 'completed')
                ->get();

            // Calculate credits earned
            $creditsEarned = $completedEnrollments->sum(function($enrollment) {
                return $enrollment->course->credits ?? 0;
            });

            // Get program requirements
            $requirements = ProgramRequirement::with('course')
                ->where('degree_program_id', $program->id)
                ->get()
                ->groupBy('requirement_type');

            // Calculate completion by requirement type
            $requirementCompletion = [];
            foreach ($requirements as $type => $typeRequirements) {
                $completed = $typeRequirements->filter(function($req) use ($completedEnrollments) {
                    return $completedEnrollments->contains('course_id', $req->course_id);
                })->count();

                $total = $typeRequirements->count();
                $mandatory = $typeRequirements->where('is_mandatory', true)->count();
                $mandatoryCompleted = $typeRequirements->where('is_mandatory', true)->filter(function($req) use ($completedEnrollments) {
                    return $completedEnrollments->contains('course_id', $req->course_id);
                })->count();

                $requirementCompletion[$type] = [
                    'total' => $total,
                    'completed' => $completed,
                    'percentage' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
                    'mandatory' => $mandatory,
                    'mandatory_completed' => $mandatoryCompleted,
                    'mandatory_percentage' => $mandatory > 0 ? round(($mandatoryCompleted / $mandatory) * 100, 2) : 0
                ];
            }

            // Calculate CGPA
            $cgpa = $this->calculateCGPA($studentId);

            // Check graduation eligibility
            $graduationEligibility = $this->checkGraduationEligibility($student, $program, $creditsEarned, $cgpa, $requirementCompletion);

            return response()->json([
                'success' => true,
                'data' => [
                    'student' => $student,
                    'program' => $program,
                    'progress' => [
                        'credits_earned' => $creditsEarned,
                        'credits_required' => $program->total_credits_required,
                        'credits_percentage' => round(($creditsEarned / $program->total_credits_required) * 100, 2),
                        'cgpa' => $cgpa,
                        'minimum_cgpa' => $program->minimum_cgpa,
                        'requirement_completion' => $requirementCompletion
                    ],
                    'graduation_eligibility' => $graduationEligibility,
                    'completed_courses' => $completedEnrollments
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching degree progress',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate student transcript
     */
    public function transcript($studentId)
    {
        try {
            $student = Student::findOrFail($studentId);
            
            // Get all enrollments grouped by semester
            $enrollments = Enrollment::with(['course', 'assignmentGrades'])
                ->where('student_id', $studentId)
                ->where('status', 'completed')
                ->orderBy('created_at')
                ->get();

            // Group by semester (you may need to add semester field to enrollments)
            $transcript = [];
            $cumulativeGradePoints = 0;
            $cumulativeCredits = 0;

            foreach ($enrollments as $enrollment) {
                $course = $enrollment->course;
                $credits = $course->credits ?? 0;
                
                // Calculate course grade
                $gradePoint = $this->calculateCourseGrade($enrollment);
                $letterGrade = $this->gradePointToLetter($gradePoint);

                $cumulativeGradePoints += ($gradePoint * $credits);
                $cumulativeCredits += $credits;

                $transcript[] = [
                    'course_code' => $course->course_code,
                    'course_name' => $course->name,
                    'credits' => $credits,
                    'grade_point' => $gradePoint,
                    'letter_grade' => $letterGrade,
                    'quality_points' => $gradePoint * $credits
                ];
            }

            $cgpa = $cumulativeCredits > 0 ? round($cumulativeGradePoints / $cumulativeCredits, 2) : 0;

            return response()->json([
                'success' => true,
                'data' => [
                    'student' => [
                        'id' => $student->id,
                        'name' => $student->name,
                        'email' => $student->email,
                        'student_id' => $student->student_id
                    ],
                    'transcript' => $transcript,
                    'summary' => [
                        'total_credits' => $cumulativeCredits,
                        'total_quality_points' => round($cumulativeGradePoints, 2),
                        'cgpa' => $cgpa,
                        'total_courses' => count($transcript)
                    ],
                    'generated_at' => now()
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating transcript',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get remaining requirements for graduation
     */
    public function remainingRequirements($studentId)
    {
        try {
            $student = Student::findOrFail($studentId);
            
            if (!$student->degree_program_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student is not enrolled in a degree program'
                ], 404);
            }

            $program = DegreeProgram::findOrFail($student->degree_program_id);
            
            // Get completed courses
            $completedCourseIds = Enrollment::where('student_id', $studentId)
                ->where('status', 'completed')
                ->pluck('course_id')
                ->toArray();

            // Get all program requirements
            $allRequirements = ProgramRequirement::with('course')
                ->where('degree_program_id', $program->id)
                ->get();

            // Filter remaining requirements
            $remaining = $allRequirements->filter(function($req) use ($completedCourseIds) {
                return !in_array($req->course_id, $completedCourseIds);
            })->groupBy('requirement_type');

            return response()->json([
                'success' => true,
                'data' => [
                    'program' => $program,
                    'remaining_requirements' => $remaining,
                    'summary' => [
                        'total_remaining' => $allRequirements->count() - count($completedCourseIds),
                        'by_type' => $remaining->map(fn($items) => $items->count())
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching remaining requirements',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate student CGPA
     */
    private function calculateCGPA($studentId)
    {
        $enrollments = Enrollment::with(['course', 'assignmentGrades'])
            ->where('student_id', $studentId)
            ->where('status', 'completed')
            ->get();

        $totalGradePoints = 0;
        $totalCredits = 0;

        foreach ($enrollments as $enrollment) {
            $credits = $enrollment->course->credits ?? 0;
            $gradePoint = $this->calculateCourseGrade($enrollment);
            
            $totalGradePoints += ($gradePoint * $credits);
            $totalCredits += $credits;
        }

        return $totalCredits > 0 ? round($totalGradePoints / $totalCredits, 2) : 0;
    }

    /**
     * Calculate grade for a single course
     */
    private function calculateCourseGrade($enrollment)
    {
        $grades = AssignmentGrade::where('student_id', $enrollment->student_id)
            ->whereHas('assignment', function($query) use ($enrollment) {
                $query->where('course_id', $enrollment->course_id);
            })
            ->get();

        if ($grades->isEmpty()) {
            return 0;
        }

        $totalPercentage = $grades->avg('percentage');
        return $this->percentageToGradePoint($totalPercentage);
    }

    /**
     * Convert percentage to grade point (5.0 scale)
     */
    private function percentageToGradePoint($percentage)
    {
        if ($percentage >= 90) return 5.00;
        if ($percentage >= 85) return 4.75;
        if ($percentage >= 80) return 4.50;
        if ($percentage >= 75) return 4.00;
        if ($percentage >= 70) return 3.50;
        if ($percentage >= 65) return 3.00;
        if ($percentage >= 60) return 2.50;
        if ($percentage >= 55) return 2.00;
        if ($percentage >= 50) return 1.50;
        if ($percentage >= 45) return 1.00;
        if ($percentage >= 40) return 0.75;
        return 0.00;
    }

    /**
     * Convert grade point to letter grade
     */
    private function gradePointToLetter($gradePoint)
    {
        if ($gradePoint >= 4.75) return 'A+';
        if ($gradePoint >= 4.50) return 'A';
        if ($gradePoint >= 4.00) return 'A-';
        if ($gradePoint >= 3.50) return 'B+';
        if ($gradePoint >= 3.00) return 'B';
        if ($gradePoint >= 2.50) return 'B-';
        if ($gradePoint >= 2.00) return 'C+';
        if ($gradePoint >= 1.50) return 'C';
        if ($gradePoint >= 1.00) return 'C-';
        if ($gradePoint >= 0.75) return 'D';
        return 'F';
    }

    /**
     * Check if student is eligible for graduation
     */
    private function checkGraduationEligibility($student, $program, $creditsEarned, $cgpa, $requirementCompletion)
    {
        $eligible = true;
        $reasons = [];

        // Check credits
        if ($creditsEarned < $program->total_credits_required) {
            $eligible = false;
            $reasons[] = 'Insufficient credits earned';
        }

        // Check CGPA
        if ($program->minimum_cgpa && $cgpa < $program->minimum_cgpa) {
            $eligible = false;
            $reasons[] = 'CGPA below minimum requirement';
        }

        // Check mandatory requirements
        foreach ($requirementCompletion as $type => $completion) {
            if ($completion['mandatory'] > 0 && $completion['mandatory_completed'] < $completion['mandatory']) {
                $eligible = false;
                $reasons[] = "Incomplete mandatory {$type} courses";
            }
        }

        return [
            'eligible' => $eligible,
            'reasons' => $reasons
        ];
    }
}
