<?php

namespace App\Services;

use App\Models\User;
use App\Models\Course;
use App\Models\Grade;
use App\Models\ImportLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CsvImportService
{
    protected array $errors = [];
    protected array $warnings = [];
    protected int $successCount = 0;
    protected int $errorCount = 0;

    /**
     * Import students from CSV
     */
    public function importStudents(string $filePath, int $userId): array
    {
        $importLog = $this->createImportLog($userId, 'students');
        
        try {
            DB::beginTransaction();

            $rows = $this->parseCsvFile($filePath);
            $headers = array_shift($rows); // Remove header row
            $totalRows = count($rows);

            // Update total_rows in import log
            $importLog->update(['total_rows' => $totalRows, 'started_at' => now()]);

            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2; // +2 because we removed header and arrays are 0-indexed
                
                try {
                    $data = $this->mapStudentData($headers, $row);
                    $this->validateStudentData($data, $rowNumber);

                    if (!isset($this->errors[$rowNumber])) {
                        $this->createStudent($data);
                        $this->successCount++;
                    }
                } catch (\Exception $e) {
                    $this->errors[$rowNumber] = $e->getMessage();
                    $this->errorCount++;
                }
            }

            if ($this->errorCount === 0) {
                DB::commit();
                $importLog->update([
                    'status' => 'completed',
                    'success_count' => $this->successCount,
                    'error_count' => $this->errorCount,
                    'errors' => json_encode($this->errors),
                    'completed_at' => now(),
                ]);
            } else {
                DB::rollBack();
                $importLog->update([
                    'status' => 'failed',
                    'success_count' => 0,
                    'error_count' => $this->errorCount,
                    'errors' => json_encode($this->errors),
                    'completed_at' => now(),
                ]);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Student import failed: ' . $e->getMessage());
            
            $importLog->update([
                'status' => 'failed',
                'error_count' => $this->errorCount,
                'errors' => json_encode(['general' => $e->getMessage()]),
                'completed_at' => now(),
            ]);
        }

        return [
            'success' => $this->errorCount === 0,
            'import_log_id' => $importLog->id,
            'success_count' => $this->successCount,
            'error_count' => $this->errorCount,
            'errors' => $this->errors,
            'warnings' => $this->warnings,
        ];
    }
    public function importCourses(string $filePath, int $userId): array
    {
        $importLog = $this->createImportLog($userId, 'courses');
        
        try {
            DB::beginTransaction();

            $rows = $this->parseCsvFile($filePath);
            $headers = array_shift($rows);
            $totalRows = count($rows);

            // Update total_rows in import log
            $importLog->update(['total_rows' => $totalRows, 'started_at' => now()]);

            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2;
                
                try {
                    $data = $this->mapCourseData($headers, $row);
                    $this->validateCourseData($data, $rowNumber);

                    if (!isset($this->errors[$rowNumber])) {
                        $this->createCourse($data);
                        $this->successCount++;
                    }
                } catch (\Exception $e) {
                    $this->errors[$rowNumber] = $e->getMessage();
                    $this->errorCount++;
                }
            }

            if ($this->errorCount === 0) {
                DB::commit();
                $importLog->update([
                    'status' => 'completed',
                    'success_count' => $this->successCount,
                    'error_count' => $this->errorCount,
                    'completed_at' => now(),
                ]);
            } else {
                DB::rollBack();
                $importLog->update([
                    'status' => 'failed',
                    'error_count' => $this->errorCount,
                    'errors' => json_encode($this->errors),
                    'completed_at' => now(),
                ]);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Course import failed: ' . $e->getMessage());
            
            $importLog->update([
                'status' => 'failed',
                'errors' => json_encode(['general' => $e->getMessage()]),
                'completed_at' => now(),
            ]);
        }

        return [
            'success' => $this->errorCount === 0,
            'import_log_id' => $importLog->id,
            'success_count' => $this->successCount,
            'error_count' => $this->errorCount,
            'errors' => $this->errors,
        ];
    }    /**
     * Import grades/results from CSV
     */
    public function importGrades(string $filePath, int $userId): array
    {
        $importLog = $this->createImportLog($userId, 'grades');
        
        try {
            DB::beginTransaction();

            $rows = $this->parseCsvFile($filePath);
            $headers = array_shift($rows);
            $totalRows = count($rows);

            // Update total_rows in import log
            $importLog->update(['total_rows' => $totalRows, 'started_at' => now()]);

            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2;
                
                try {
                    $data = $this->mapGradeData($headers, $row);
                    $this->validateGradeData($data, $rowNumber);

                    if (!isset($this->errors[$rowNumber])) {
                        $this->createOrUpdateGrade($data);
                        $this->successCount++;
                    }
                } catch (\Exception $e) {
                    $this->errors[$rowNumber] = $e->getMessage();
                    $this->errorCount++;
                }
            }

            if ($this->errorCount === 0) {
                DB::commit();
                $importLog->update([
                    'status' => 'completed',
                    'success_count' => $this->successCount,
                    'error_count' => $this->errorCount,
                    'completed_at' => now(),
                ]);
            } else {
                DB::rollBack();
                $importLog->update([
                    'status' => 'failed',
                    'error_count' => $this->errorCount,
                    'errors' => json_encode($this->errors),
                    'completed_at' => now(),
                ]);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Grade import failed: ' . $e->getMessage());
            
            $importLog->update([
                'status' => 'failed',
                'errors' => json_encode(['general' => $e->getMessage()]),
                'completed_at' => now(),
            ]);
        }

        return [
            'success' => $this->errorCount === 0,
            'import_log_id' => $importLog->id,
            'success_count' => $this->successCount,
            'error_count' => $this->errorCount,
            'errors' => $this->errors,
        ];
    }

    /**
     * Parse CSV file and return rows
     */
    protected function parseCsvFile(string $filePath): array
    {
        if (!file_exists($filePath)) {
            throw new \Exception('File not found');
        }

        $rows = [];
        $handle = fopen($filePath, 'r');

        if ($handle === false) {
            throw new \Exception('Cannot open file');
        }

        while (($row = fgetcsv($handle)) !== false) {
            $rows[] = $row;
        }

        fclose($handle);

        if (empty($rows)) {
            throw new \Exception('CSV file is empty');
        }

        return $rows;
    }

    /**
     * Map CSV row to student data
     */
    protected function mapStudentData(array $headers, array $row): array
    {
        $data = [];
        
        foreach ($headers as $index => $header) {
            $header = strtolower(trim($header));
            $value = isset($row[$index]) ? trim($row[$index]) : null;
            
            $data[$header] = $value;
        }

        return [
            'name' => $data['name'] ?? $data['full_name'] ?? null,
            'email' => $data['email'] ?? null,
            'student_id' => $data['student_id'] ?? $data['id'] ?? null,
            'phone' => $data['phone'] ?? $data['phone_number'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? $data['dob'] ?? null,
            'department_id' => $data['department_id'] ?? $data['department'] ?? null,
            'program_id' => $data['program_id'] ?? $data['program'] ?? null,
            'entry_year' => $data['entry_year'] ?? $data['year'] ?? now()->year,
        ];
    }

    /**
     * Map CSV row to course data
     */
    protected function mapCourseData(array $headers, array $row): array
    {
        $data = [];
        
        foreach ($headers as $index => $header) {
            $header = strtolower(trim($header));
            $value = isset($row[$index]) ? trim($row[$index]) : null;
            
            $data[$header] = $value;
        }

        return [
            'course_code' => $data['course_code'] ?? $data['code'] ?? null,
            'course_name' => $data['course_name'] ?? $data['name'] ?? null,
            'credits' => $data['credits'] ?? $data['credit_hours'] ?? 3,
            'department_id' => $data['department_id'] ?? $data['department'] ?? null,
            'semester' => $data['semester'] ?? null,
            'level' => $data['level'] ?? $data['year'] ?? null,
            'description' => $data['description'] ?? null,
        ];
    }

    /**
     * Map CSV row to grade data
     */
    protected function mapGradeData(array $headers, array $row): array
    {
        $data = [];
        
        foreach ($headers as $index => $header) {
            $header = strtolower(trim($header));
            $value = isset($row[$index]) ? trim($row[$index]) : null;
            
            $data[$header] = $value;
        }

        return [
            'student_id' => $data['student_id'] ?? $data['id'] ?? null,
            'course_code' => $data['course_code'] ?? $data['code'] ?? null,
            'exam_score' => $data['exam_score'] ?? $data['exam'] ?? null,
            'ca_score' => $data['ca_score'] ?? $data['ca'] ?? $data['continuous_assessment'] ?? null,
            'total_score' => $data['total_score'] ?? $data['total'] ?? null,
            'grade' => $data['grade'] ?? null,
            'semester_id' => $data['semester_id'] ?? $data['semester'] ?? null,
            'academic_year' => $data['academic_year'] ?? $data['year'] ?? now()->year,
        ];
    }

    /**
     * Validate student data
     */
    protected function validateStudentData(array $data, int $rowNumber): void
    {
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'student_id' => 'required|unique:users,student_id',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'department_id' => 'nullable|exists:departments,id',
            'entry_year' => 'required|integer|min:2000|max:' . (now()->year + 1),
        ]);

        if ($validator->fails()) {
            $this->errors[$rowNumber] = $validator->errors()->first();
            throw new \Exception($validator->errors()->first());
        }
    }

    /**
     * Validate course data
     */
    protected function validateCourseData(array $data, int $rowNumber): void
    {
        $validator = Validator::make($data, [
            'course_code' => 'required|string|unique:courses,course_code|max:20',
            'course_name' => 'required|string|max:255',
            'credits' => 'required|integer|min:1|max:6',
            'department_id' => 'required|exists:departments,id',
            'semester' => 'nullable|integer|min:1|max:3',
            'level' => 'nullable|integer|min:100|max:500',
        ]);

        if ($validator->fails()) {
            $this->errors[$rowNumber] = $validator->errors()->first();
            throw new \Exception($validator->errors()->first());
        }
    }

    /**
     * Validate grade data
     */
    protected function validateGradeData(array $data, int $rowNumber): void
    {
        // First verify student exists
        $student = User::where('student_id', $data['student_id'])->first();
        if (!$student) {
            $this->errors[$rowNumber] = "Student ID {$data['student_id']} not found";
            throw new \Exception("Student ID {$data['student_id']} not found");
        }

        // Verify course exists
        $course = Course::where('course_code', $data['course_code'])->first();
        if (!$course) {
            $this->errors[$rowNumber] = "Course code {$data['course_code']} not found";
            throw new \Exception("Course code {$data['course_code']} not found");
        }

        $validator = Validator::make($data, [
            'student_id' => 'required|string',
            'course_code' => 'required|string',
            'exam_score' => 'nullable|numeric|min:0|max:100',
            'ca_score' => 'nullable|numeric|min:0|max:100',
            'total_score' => 'nullable|numeric|min:0|max:100',
            'grade' => 'nullable|string|max:2',
            'semester_id' => 'nullable|exists:semesters,id',
            'academic_year' => 'required|integer|min:2000|max:' . (now()->year + 1),
        ]);

        if ($validator->fails()) {
            $this->errors[$rowNumber] = $validator->errors()->first();
            throw new \Exception($validator->errors()->first());
        }
    }

    /**
     * Create student user
     */
    protected function createStudent(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'student_id' => $data['student_id'],
            'phone' => $data['phone'],
            'date_of_birth' => $data['date_of_birth'] ? Carbon::parse($data['date_of_birth']) : null,
            'department_id' => $data['department_id'],
            'program_id' => $data['program_id'],
            'entry_year' => $data['entry_year'],
            'role' => 'student',
            'password' => Hash::make('password'), // Default password, should be changed
            'password_changed' => false,
        ]);
    }

    /**
     * Create course
     */
    protected function createCourse(array $data): Course
    {
        return Course::create([
            'course_code' => $data['course_code'],
            'course_name' => $data['course_name'],
            'credits' => $data['credits'],
            'department_id' => $data['department_id'],
            'semester' => $data['semester'],
            'level' => $data['level'],
            'description' => $data['description'],
        ]);
    }

    /**
     * Create or update grade
     */
    protected function createOrUpdateGrade(array $data): Grade
    {
        // Get student by student_id
        $student = User::where('student_id', $data['student_id'])->first();
        
        // Get course by course_code
        $course = Course::where('course_code', $data['course_code'])->first();

        // Calculate total if not provided
        $totalScore = $data['total_score'];
        if (!$totalScore && $data['exam_score'] && $data['ca_score']) {
            $totalScore = $data['exam_score'] + $data['ca_score'];
        }

        // Determine grade letter if not provided
        $gradeLetter = $data['grade'];
        if (!$gradeLetter && $totalScore) {
            $gradeLetter = $this->calculateGradeLetter($totalScore);
        }

        // Find existing grade record
        $grade = Grade::where('student_id', $student->id)
            ->where('course_id', $course->id)
            ->where('assessment_type', 'final')
            ->first();

        $gradeData = [
            'student_id' => $student->id,
            'course_id' => $course->id,
            'assessment_type' => 'final',
            'assessment_name' => 'Final Exam',
            'max_marks' => 100,
            'obtained_marks' => $totalScore,
            'grade_letter' => $gradeLetter,
            'grade_point' => $this->calculateGradePoint($gradeLetter),
            'assessment_date' => now(),
        ];

        if ($grade) {
            // Update existing grade
            $grade->update($gradeData);
        } else {
            // Create new grade
            $grade = Grade::create($gradeData);
        }

        return $grade;
    }

    /**
     * Calculate grade letter from score
     */
    protected function calculateGradeLetter(float $score): string
    {
        if ($score >= 90) return 'A+';
        if ($score >= 85) return 'A';
        if ($score >= 80) return 'A-';
        if ($score >= 75) return 'B+';
        if ($score >= 70) return 'B';
        if ($score >= 65) return 'B-';
        if ($score >= 60) return 'C+';
        if ($score >= 55) return 'C';
        if ($score >= 50) return 'C-';
        if ($score >= 45) return 'D+';
        if ($score >= 40) return 'D';
        return 'F';
    }

    /**
     * Calculate grade point from letter
     */
    protected function calculateGradePoint(string $letter): float
    {
        $gradePoints = [
            'A+' => 4.0,
            'A' => 4.0,
            'A-' => 3.7,
            'B+' => 3.3,
            'B' => 3.0,
            'B-' => 2.7,
            'C+' => 2.3,
            'C' => 2.0,
            'C-' => 1.7,
            'D+' => 1.3,
            'D' => 1.0,
            'F' => 0.0,
        ];

        return $gradePoints[$letter] ?? 0.0;
    }

    /**
     * Create import log
     */
    protected function createImportLog(int $userId, string $type): ImportLog
    {
        return ImportLog::create([
            'user_id' => $userId,
            'type' => $type,
            'status' => 'processing',
            'success_count' => 0,
            'error_count' => 0,
            'started_at' => now(),
        ]);
    }

    /**
     * Get CSV template for students
     */
    public function getStudentTemplate(): string
    {
        return "name,email,student_id,phone,date_of_birth,department_id,program_id,entry_year\n" .
               "John Doe,john.doe@example.com,STU001,+1234567890,2000-01-15,1,1,2024\n" .
               "Jane Smith,jane.smith@example.com,STU002,+1234567891,2001-03-22,1,1,2024";
    }

    /**
     * Get CSV template for courses
     */
    public function getCourseTemplate(): string
    {
        return "course_code,course_name,credits,department_id,semester,level,description\n" .
               "CS101,Introduction to Computer Science,3,1,1,100,Basic programming concepts\n" .
               "MATH201,Calculus I,4,2,1,200,Differential and integral calculus";
    }

    /**
     * Get CSV template for grades
     */
    public function getGradeTemplate(): string
    {
        return "student_id,course_code,exam_score,ca_score,total_score,grade,semester_id,academic_year\n" .
               "STU001,CS101,75,20,95,A,1,2024\n" .
               "STU002,MATH201,68,18,86,A-,1,2024";
    }

    /**
     * Reset counters for new import
     */
    public function resetCounters(): void
    {
        $this->errors = [];
        $this->warnings = [];
        $this->successCount = 0;
        $this->errorCount = 0;
    }
}
