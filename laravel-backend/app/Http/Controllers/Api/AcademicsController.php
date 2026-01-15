<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ContinuousAssessment;
use App\Models\FinalExam;
use App\Models\SemesterSummary;
use App\Models\Enrollment;
use App\Models\Grade;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class AcademicsController extends Controller
{
    /**
     * Get current semester academic performance.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCurrentSemesterPerformance()
    {
        $student = Auth::user()->student;
        
        if (!$student) {
            return response()->json(['message' => 'Student profile not found'], 404);
        }

        $currentSemester = $this->getCurrentSemesterCode();

        // Get enrolled courses
        $enrollments = Enrollment::where('student_id', $student->id)
            ->where('semester', $currentSemester)
            ->with(['course'])
            ->get();

        $coursePerformance = [];

        foreach ($enrollments as $enrollment) {
            $course = $enrollment->course;
            
            // Get CA scores
            $caScores = ContinuousAssessment::where('student_id', $student->id)
                ->where('course_id', $course->id)
                ->where('semester_code', $currentSemester)
                ->get();

            $totalCA = $caScores->sum('weighted_score');

            // Get final exam
            $finalExam = FinalExam::where('student_id', $student->id)
                ->where('course_id', $course->id)
                ->where('semester_code', $currentSemester)
                ->first();

            $finalScore = $finalExam ? $finalExam->score : 0;
            $totalScore = $totalCA + $finalScore;

            $coursePerformance[] = [
                'course_id' => $course->id,
                'course_code' => $course->course_code,
                'course_title' => $course->course_name,
                'units' => $course->credits,
                'ca_score' => round($totalCA, 2),
                'ca_max' => 30,
                'final_exam_score' => round($finalScore, 2),
                'final_exam_max' => 70,
                'total_score' => round($totalScore, 2),
                'total_max' => 100,
                'grade' => $this->calculateGrade($totalScore),
                'ca_breakdown' => $caScores->map(function ($ca) {
                    return [
                        'type' => $ca->assessment_type,
                        'number' => $ca->assessment_number,
                        'score' => $ca->score,
                        'max_score' => $ca->max_score,
                        'weight' => $ca->weight,
                        'weighted_score' => $ca->weighted_score,
                        'percentage' => $ca->percentage,
                    ];
                }),
            ];
        }

        // Calculate current semester GPA
        $semesterGPA = $this->calculateSemesterGPA($coursePerformance);

        // Get or create semester summary
        $summary = SemesterSummary::firstOrCreate(
            [
                'student_id' => $student->id,
                'semester_code' => $currentSemester,
            ],
            [
                'academic_year' => $this->getAcademicYear($currentSemester),
                'total_courses' => $enrollments->count(),
                'total_units' => $enrollments->sum(fn($e) => $e->course->credits ?? 0),
                'semester_gpa' => $semesterGPA,
            ]
        );

        return response()->json([
            'semester_code' => $currentSemester,
            'course_performance' => $coursePerformance,
            'semester_gpa' => $semesterGPA,
            'total_courses' => $enrollments->count(),
            'total_units' => $enrollments->sum(fn($e) => $e->course->credits ?? 0),
            'summary' => $summary,
        ]);
    }

    /**
     * Get detailed breakdown for a specific course.
     * 
     * @param int $courseId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCourseBreakdown($courseId)
    {
        $student = Auth::user()->student;
        
        if (!$student) {
            return response()->json(['message' => 'Student profile not found'], 404);
        }

        $currentSemester = $this->getCurrentSemesterCode();

        // Verify enrollment
        $enrollment = Enrollment::where('student_id', $student->id)
            ->where('course_id', $courseId)
            ->where('semester', $currentSemester)
            ->with(['course'])
            ->firstOrFail();

        // Get all CA scores
        $caScores = ContinuousAssessment::where('student_id', $student->id)
            ->where('course_id', $courseId)
            ->where('semester_code', $currentSemester)
            ->orderBy('assessment_type')
            ->orderBy('assessment_number')
            ->get();

        // Group by type
        $caByType = $caScores->groupBy('assessment_type')->map(function ($group) {
            return [
                'assessments' => $group->map(function ($ca) {
                    return [
                        'id' => $ca->id,
                        'number' => $ca->assessment_number,
                        'score' => $ca->score,
                        'max_score' => $ca->max_score,
                        'weight' => $ca->weight,
                        'weighted_score' => $ca->weighted_score,
                        'percentage' => $ca->percentage,
                        'date' => $ca->assessment_date,
                        'remarks' => $ca->remarks,
                    ];
                }),
                'subtotal' => $group->sum('weighted_score'),
                'count' => $group->count(),
            ];
        });

        // Get final exam
        $finalExam = FinalExam::where('student_id', $student->id)
            ->where('course_id', $courseId)
            ->where('semester_code', $currentSemester)
            ->first();

        $totalCA = $caScores->sum('weighted_score');
        $finalScore = $finalExam ? $finalExam->score : 0;
        $totalScore = $totalCA + $finalScore;

        return response()->json([
            'course' => $enrollment->course,
            'continuous_assessment' => [
                'by_type' => $caByType,
                'total' => round($totalCA, 2),
                'max' => 30,
                'percentage' => round(($totalCA / 30) * 100, 2),
            ],
            'final_exam' => [
                'score' => round($finalScore, 2),
                'max' => 70,
                'percentage' => round(($finalScore / 70) * 100, 2),
                'date' => $finalExam?->exam_date,
                'venue' => $finalExam?->exam_venue,
                'status' => $finalExam?->status ?? 'pending',
            ],
            'total_score' => round($totalScore, 2),
            'grade' => $this->calculateGrade($totalScore),
            'grade_point' => $this->getGradePoint($this->calculateGrade($totalScore)),
        ]);
    }

    /**
     * Get historical semester records.
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHistoricalRecords()
    {
        $student = Auth::user()->student;
        
        if (!$student) {
            return response()->json(['message' => 'Student profile not found'], 404);
        }

        $summaries = SemesterSummary::where('student_id', $student->id)
            ->orderBy('semester_code', 'desc')
            ->get();

        return response()->json([
            'historical_records' => $summaries,
            'total_semesters' => $summaries->count(),
            'current_cgpa' => $summaries->last()?->cumulative_gpa ?? 0,
            'total_units_earned' => $summaries->last()?->total_units_earned ?? 0,
        ]);
    }

    /**
     * Get specific semester performance.
     * 
     * @param string $semesterCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSemesterPerformance($semesterCode)
    {
        $student = Auth::user()->student;
        
        if (!$student) {
            return response()->json(['message' => 'Student profile not found'], 404);
        }

        // Get semester summary
        $summary = SemesterSummary::where('student_id', $student->id)
            ->where('semester_code', $semesterCode)
            ->first();

        if (!$summary) {
            return response()->json([
                'message' => 'No records found for this semester',
                'semester_code' => $semesterCode,
            ], 404);
        }

        // Get courses for that semester
        $enrollments = Enrollment::where('student_id', $student->id)
            ->where('semester', $semesterCode)
            ->with(['course'])
            ->get();

        $courseDetails = [];
        foreach ($enrollments as $enrollment) {
            $course = $enrollment->course;
            
            $totalCA = ContinuousAssessment::where('student_id', $student->id)
                ->where('course_id', $course->id)
                ->where('semester_code', $semesterCode)
                ->sum('weighted_score');

            $finalExam = FinalExam::where('student_id', $student->id)
                ->where('course_id', $course->id)
                ->where('semester_code', $semesterCode)
                ->first();

            $finalScore = $finalExam ? $finalExam->score : 0;
            $totalScore = $totalCA + $finalScore;

            $courseDetails[] = [
                'course_code' => $course->course_code,
                'course_title' => $course->course_name,
                'units' => $course->credits,
                'ca_score' => round($totalCA, 2),
                'exam_score' => round($finalScore, 2),
                'total_score' => round($totalScore, 2),
                'grade' => $this->calculateGrade($totalScore),
                'grade_point' => $this->getGradePoint($this->calculateGrade($totalScore)),
            ];
        }

        return response()->json([
            'semester_code' => $semesterCode,
            'academic_year' => $summary->academic_year,
            'summary' => $summary,
            'courses' => $courseDetails,
        ]);
    }

    /**
     * Download transcript PDF.
     * 
     * @return \Illuminate\Http\Response
     */
    public function downloadTranscript()
    {
        $student = Auth::user()->student;
        
        if (!$student) {
            return response()->json(['message' => 'Student profile not found'], 404);
        }

        // Get all semester summaries
        $summaries = SemesterSummary::where('student_id', $student->id)
            ->orderBy('semester_code')
            ->get();

        // Generate PDF
        $pdf = Pdf::loadView('transcripts.pdf', [
            'student' => $student,
            'summaries' => $summaries,
            'generated_date' => now(),
        ]);
        
        return $pdf->download("transcript_{$student->matric_no}.pdf");
    }

    /**
     * Get GPA summary (current and cumulative).
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGPASummary()
    {
        $student = Auth::user()->student;
        
        if (!$student) {
            return response()->json(['message' => 'Student profile not found'], 404);
        }

        $summaries = SemesterSummary::where('student_id', $student->id)
            ->orderBy('semester_code')
            ->get();

        $currentSemester = $summaries->last();

        return response()->json([
            'current_semester_gpa' => $currentSemester?->semester_gpa ?? 0,
            'cumulative_gpa' => $currentSemester?->cumulative_gpa ?? 0,
            'total_units_earned' => $currentSemester?->total_units_earned ?? 0,
            'total_semesters' => $summaries->count(),
            'academic_standing' => $currentSemester?->semester_status ?? 'unknown',
            'is_good_standing' => $currentSemester?->isGoodStanding() ?? false,
            'is_on_probation' => $currentSemester?->isOnProbation() ?? false,
            'semester_history' => $summaries->map(function ($summary) {
                return [
                    'semester_code' => $summary->semester_code,
                    'semester_gpa' => $summary->semester_gpa,
                    'cumulative_gpa' => $summary->cumulative_gpa,
                    'total_units' => $summary->total_units,
                    'status' => $summary->semester_status,
                ];
            }),
        ]);
    }

    /**
     * Helper: Calculate grade from total score.
     */
    private function calculateGrade(float $score): string
    {
        if ($score >= 70) return 'A';
        if ($score >= 60) return 'B';
        if ($score >= 50) return 'C';
        if ($score >= 45) return 'D';
        if ($score >= 40) return 'E';
        return 'F';
    }

    /**
     * Helper: Get grade point from letter grade.
     */
    private function getGradePoint(string $grade): float
    {
        $gradePoints = [
            'A' => 5.0,
            'B' => 4.0,
            'C' => 3.0,
            'D' => 2.0,
            'E' => 1.0,
            'F' => 0.0,
        ];

        return $gradePoints[$grade] ?? 0.0;
    }

    /**
     * Helper: Calculate semester GPA.
     */
    private function calculateSemesterGPA(array $coursePerformance): float
    {
        $totalPoints = 0;
        $totalUnits = 0;

        foreach ($coursePerformance as $course) {
            $grade = $course['grade'];
            $units = $course['units'];
            $gradePoint = $this->getGradePoint($grade);
            
            $totalPoints += $gradePoint * $units;
            $totalUnits += $units;
        }

        return $totalUnits > 0 ? round($totalPoints / $totalUnits, 2) : 0.0;
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
