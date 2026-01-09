<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Enrollment;
use App\Models\AssignmentGrade;
use App\Models\GradePoint;
use App\Models\Course;
use Illuminate\Support\Facades\DB;

class GpaCalculationService
{
    /**
     * Calculate semester GPA for a student
     */
    public function calculateSemesterGpa(int $studentId, int $semester): array
    {
        $enrollments = Enrollment::where('student_id', $studentId)
            ->whereHas('course', function ($query) use ($semester) {
                $query->where('semester', $semester);
            })
            ->with('course')
            ->get();

        if ($enrollments->isEmpty()) {
            return [
                'semester' => $semester,
                'gpa' => 0.00,
                'total_credits' => 0,
                'courses' => [],
                'quality_points' => 0.00
            ];
        }

        $totalCredits = 0;
        $totalQualityPoints = 0;
        $courses = [];

        foreach ($enrollments as $enrollment) {
            $course = $enrollment->course;
            $finalGrade = $this->calculateCourseGrade($studentId, $course->id);
            
            if ($finalGrade['letter_grade'] && $finalGrade['grade_point'] !== null) {
                $credits = $course->credits ?? 3; // Default to 3 credits if not set
                $qualityPoints = $finalGrade['grade_point'] * $credits;

                $totalCredits += $credits;
                $totalQualityPoints += $qualityPoints;

                $courses[] = [
                    'course_id' => $course->id,
                    'course_code' => $course->course_code,
                    'course_name' => $course->name,
                    'credits' => $credits,
                    'percentage' => $finalGrade['percentage'],
                    'letter_grade' => $finalGrade['letter_grade'],
                    'grade_point' => $finalGrade['grade_point'],
                    'quality_points' => $qualityPoints
                ];
            }
        }

        $semesterGpa = $totalCredits > 0 ? round($totalQualityPoints / $totalCredits, 2) : 0.00;

        return [
            'semester' => $semester,
            'gpa' => $semesterGpa,
            'total_credits' => $totalCredits,
            'quality_points' => $totalQualityPoints,
            'courses' => $courses
        ];
    }

    /**
     * Calculate cumulative GPA (CGPA) for a student
     */
    public function calculateCumulativeGpa(int $studentId): array
    {
        $enrollments = Enrollment::where('student_id', $studentId)
            ->with('course')
            ->get();

        if ($enrollments->isEmpty()) {
            return [
                'cgpa' => 0.00,
                'total_credits' => 0,
                'total_quality_points' => 0.00,
                'semesters' => []
            ];
        }

        $totalCredits = 0;
        $totalQualityPoints = 0;
        $semesterData = [];

        // Group by semester
        $enrollmentsBySemester = $enrollments->groupBy(function ($enrollment) {
            return $enrollment->course->semester ?? 1;
        });

        foreach ($enrollmentsBySemester as $semester => $semesterEnrollments) {
            $semesterGpa = $this->calculateSemesterGpa($studentId, $semester);
            
            $totalCredits += $semesterGpa['total_credits'];
            $totalQualityPoints += $semesterGpa['quality_points'];
            
            $semesterData[] = $semesterGpa;
        }

        $cgpa = $totalCredits > 0 ? round($totalQualityPoints / $totalCredits, 2) : 0.00;

        return [
            'cgpa' => $cgpa,
            'total_credits' => $totalCredits,
            'total_quality_points' => $totalQualityPoints,
            'semesters' => $semesterData,
            'academic_standing' => $this->getAcademicStanding($cgpa)
        ];
    }

    /**
     * Calculate final grade for a specific course
     */
    public function calculateCourseGrade(int $studentId, int $courseId): array
    {
        // Get all assignment grades for this student in this course
        $assignmentGrades = AssignmentGrade::where('student_id', $studentId)
            ->whereHas('assignment', function ($query) use ($courseId) {
                $query->where('course_id', $courseId);
            })
            ->with('assignment')
            ->get();

        if ($assignmentGrades->isEmpty()) {
            return [
                'percentage' => 0.00,
                'letter_grade' => null,
                'grade_point' => null,
                'assignments' => []
            ];
        }

        // Calculate weighted average
        $totalWeight = 0;
        $weightedScore = 0;
        $assignments = [];

        foreach ($assignmentGrades as $grade) {
            $assignment = $grade->assignment;
            $weight = $assignment->total_marks ?? 100;
            $score = $grade->marks_obtained ?? 0;
            $maxMarks = $assignment->total_marks ?? 100;

            // Convert to percentage for this assignment
            $assignmentPercentage = ($score / $maxMarks) * 100;
            
            $weightedScore += ($assignmentPercentage * $weight);
            $totalWeight += $weight;

            $assignments[] = [
                'assignment_title' => $assignment->title,
                'marks_obtained' => $score,
                'total_marks' => $maxMarks,
                'percentage' => round($assignmentPercentage, 2),
                'weight' => $weight
            ];
        }

        // Calculate final percentage
        $finalPercentage = $totalWeight > 0 ? round($weightedScore / $totalWeight, 2) : 0.00;

        // Get letter grade and grade point
        $gradeInfo = $this->getLetterGrade($finalPercentage);

        return [
            'percentage' => $finalPercentage,
            'letter_grade' => $gradeInfo['letter_grade'],
            'grade_point' => $gradeInfo['grade_point'],
            'description' => $gradeInfo['description'],
            'is_passing' => $gradeInfo['is_passing'],
            'assignments' => $assignments
        ];
    }

    /**
     * Get letter grade and grade point from percentage
     */
    public function getLetterGrade(float $percentage): array
    {
        $gradePoint = GradePoint::where('min_percentage', '<=', $percentage)
            ->where('max_percentage', '>=', $percentage)
            ->first();

        if (!$gradePoint) {
            // Default to F if no grade point found
            return [
                'letter_grade' => 'F',
                'grade_point' => 0.00,
                'description' => 'Fail',
                'is_passing' => false
            ];
        }

        return [
            'letter_grade' => $gradePoint->letter_grade,
            'grade_point' => $gradePoint->grade_point,
            'description' => $gradePoint->description,
            'is_passing' => $gradePoint->is_passing
        ];
    }

    /**
     * Get academic standing based on CGPA
     */
    public function getAcademicStanding(float $cgpa): string
    {
        if ($cgpa >= 4.75) {
            return 'Dean\'s List / Summa Cum Laude';
        } elseif ($cgpa >= 4.50) {
            return 'Magna Cum Laude';
        } elseif ($cgpa >= 4.00) {
            return 'Cum Laude';
        } elseif ($cgpa >= 3.50) {
            return 'Good Standing';
        } elseif ($cgpa >= 3.00) {
            return 'Satisfactory Standing';
        } elseif ($cgpa >= 2.50) {
            return 'Academic Warning';
        } else {
            return 'Academic Probation';
        }
    }

    /**
     * Calculate GPA for multiple students (for ranking/comparison)
     */
    public function calculateBatchGpa(array $studentIds): array
    {
        $results = [];

        foreach ($studentIds as $studentId) {
            $gpaData = $this->calculateCumulativeGpa($studentId);
            $student = Student::with('user')->find($studentId);

            if ($student) {
                $results[] = [
                    'student_id' => $studentId,
                    'student_name' => $student->user->name ?? 'Unknown',
                    'student_code' => $student->student_id,
                    'cgpa' => $gpaData['cgpa'],
                    'total_credits' => $gpaData['total_credits'],
                    'academic_standing' => $gpaData['academic_standing']
                ];
            }
        }

        // Sort by CGPA descending
        usort($results, function ($a, $b) {
            return $b['cgpa'] <=> $a['cgpa'];
        });

        // Add rank
        foreach ($results as $index => &$result) {
            $result['rank'] = $index + 1;
        }

        return $results;
    }

    /**
     * Get GPA statistics for a course
     */
    public function getCourseGpaStatistics(int $courseId): array
    {
        $enrollments = Enrollment::where('course_id', $courseId)
            ->with('student.user')
            ->get();

        if ($enrollments->isEmpty()) {
            return [
                'total_students' => 0,
                'average_grade' => 0.00,
                'highest_grade' => 0.00,
                'lowest_grade' => 0.00,
                'pass_rate' => 0.00,
                'grade_distribution' => []
            ];
        }

        $grades = [];
        $gradeDistribution = [];

        foreach ($enrollments as $enrollment) {
            $courseGrade = $this->calculateCourseGrade($enrollment->student_id, $courseId);
            
            if ($courseGrade['percentage'] > 0) {
                $grades[] = $courseGrade['percentage'];
                
                $letterGrade = $courseGrade['letter_grade'] ?? 'N/A';
                if (!isset($gradeDistribution[$letterGrade])) {
                    $gradeDistribution[$letterGrade] = 0;
                }
                $gradeDistribution[$letterGrade]++;
            }
        }

        $totalStudents = count($grades);
        $passingGrades = array_filter($grades, function ($grade) {
            $gradeInfo = $this->getLetterGrade($grade);
            return $gradeInfo['is_passing'];
        });

        return [
            'total_students' => $totalStudents,
            'average_grade' => $totalStudents > 0 ? round(array_sum($grades) / $totalStudents, 2) : 0.00,
            'highest_grade' => $totalStudents > 0 ? round(max($grades), 2) : 0.00,
            'lowest_grade' => $totalStudents > 0 ? round(min($grades), 2) : 0.00,
            'pass_rate' => $totalStudents > 0 ? round((count($passingGrades) / $totalStudents) * 100, 2) : 0.00,
            'grade_distribution' => $gradeDistribution
        ];
    }

    /**
     * Update student's current CGPA in the database
     */
    public function updateStudentCgpa(int $studentId): bool
    {
        $gpaData = $this->calculateCumulativeGpa($studentId);
        
        $student = Student::find($studentId);
        if ($student) {
            $student->current_gpa = $gpaData['cgpa'];
            $student->total_credits = $gpaData['total_credits'];
            return $student->save();
        }

        return false;
    }
}
