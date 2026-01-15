<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\StudentInsurance;
use App\Models\EnrollmentConfirmation;
use App\Models\EnrollmentConfirmationCourse;
use App\Models\ContinuousAssessment;
use App\Models\FinalExam;
use App\Models\SemesterSummary;
use App\Models\StudentAccommodation;
use App\Models\AccommodationRoommate;
use App\Models\AccommodationFee;
use App\Models\AccommodationAmenity;
use App\Models\StudentFeedback;
use App\Models\FeedbackResponse;
use App\Models\FeedbackAttachment;

class StudentModuleEnhancementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding Student Module Enhancement data...');
        
        // Get existing students (or create if none exist)
        $students = Student::limit(10)->get();
        
        if ($students->isEmpty()) {
            $this->command->warn('No students found. Please run the main database seeder first.');
            return;
        }
        
        $this->command->info("Found {$students->count()} students to enhance...");
        
        foreach ($students as $student) {
            $this->seedStudentData($student);
        }
        
        // Seed hostel amenities (independent of students)
        $this->seedHostelAmenities();
        
        $this->command->info('Student Module Enhancement seeding completed!');
    }
    
    /**
     * Seed all enhancement data for a single student
     */
    private function seedStudentData(Student $student): void
    {
        $this->command->info("  Seeding data for student ID: {$student->id}");
        
        // 1. Registration
        try {
            Registration::create([
                'student_id' => $student->id,
                'semester_code' => '2025/2026-1',
                'academic_year' => '2025/2026',
                'registration_date' => now()->subMonths(3)->format('Y-m-d'),
                'status' => 'verified',
                'total_fees' => 250000.00,
                'amount_paid' => 150000.00,
                'balance' => 100000.00,
                'fees_verified' => true,
                'insurance_verified' => true,
                'verification_date' => now()->subMonths(2)->format('Y-m-d'),
                'verified_by' => 1,
            ]);
        } catch (\Exception $e) {
            $this->command->warn("    ⚠  Registration: " . $e->getMessage());
        }
        
        // 2. Insurance
        try {
            StudentInsurance::create([
                'student_id' => $student->id,
                'semester_code' => '2025/2026-1',
                'academic_year' => '2025/2026',
                'provider' => 'NHI',
                'policy_number' => 'INS-' . str_pad($student->id, 8, '0', STR_PAD_LEFT),
                'expiry_date' => now()->addMonths(6)->format('Y-m-d'),
                'document_path' => 'insurance_documents/sample_' . $student->id . '.pdf',
                'submission_date' => now()->subMonths(2)->format('Y-m-d'),
                'status' => 'verified',
                'verification_date' => now()->subMonths(2)->format('Y-m-d'),
                'verified_by' => 1,
            ]);
        } catch (\Exception $e) {
            $this->command->warn("    ⚠  Insurance: " . $e->getMessage());
        }
        
        // 3. Get student enrollments for remaining data
        $enrollments = Enrollment::where('student_id', $student->id)->get();
        
        if ($enrollments->isEmpty()) {
            $this->command->warn("    ⚠  No enrollments found for student");
            return;
        }
        
        // 4. Enrollment Confirmation
        try {
            $confirmation = EnrollmentConfirmation::create([
                'student_id' => $student->id,
                'semester_code' => '2025/2026-1',
                'confirmation_date' => now()->subMonths(2)->format('Y-m-d'),
                'total_units' => $enrollments->count() * 3,
                'timetable_understood' => true,
                'attendance_policy_agreed' => true,
                'academic_calendar_checked' => true,
                'confirmation_email_sent' => true,
                'email_sent_at' => now()->subMonths(2),
            ]);
            
            // Add courses to confirmation
            foreach ($enrollments as $enrollment) {
                EnrollmentConfirmationCourse::create([
                    'enrollment_confirmation_id' => $confirmation->id,
                    'course_id' => $enrollment->course_id,
                    'prerequisites_met' => true,
                    'no_schedule_conflict' => true,
                ]);
            }
        } catch (\Exception $e) {
            $this->command->warn("    ⚠  Enrollment Confirmation: " . $e->getMessage());
        }
        
        // 5. Student Feedback
        try {
            $feedback = StudentFeedback::create([
                'student_id' => $student->id,
                'ticket_number' => 'FB-' . date('Y') . '-' . str_pad(StudentFeedback::count() + 1, 4, '0', STR_PAD_LEFT),
                'category' => 'general',
                'priority' => 'medium',
                'subject' => 'Test feedback ticket',
                'message' => 'This is a test feedback message for testing purposes.',
                'status' => 'submitted',
                'submitted_at' => now()->subDays(5),
                'last_updated_at' => now()->subDays(5),
            ]);
        } catch (\Exception $e) {
            $this->command->warn("    ⚠  Feedback: " . $e->getMessage());
        }
        
        $this->command->info("    ✓ Completed");
    }
    
    /**
     * Seed registration and insurance data
     */
    private function seedRegistrationData(Student $student): void
    {
        // Create current semester registration
        Registration::create([
            'student_id' => $student->id,
            'semester_code' => '2025/2026-1',
            'academic_year' => '2025/2026',
            'registration_date' => now()->subMonths(3)->format('Y-m-d'),
            'status' => 'verified',
            'total_fees' => 250000.00,
            'amount_paid' => 150000.00,
            'balance' => 100000.00,
            'fees_verified' => true,
            'insurance_verified' => true,
            'verification_date' => now()->subMonths(2)->format('Y-m-d'),
            'verified_by' => 1, // Admin user
        ]);
        
        // Create previous semester registration
        Registration::create([
            'student_id' => $student->id,
            'semester_code' => '2024/2025-2',
            'academic_year' => '2024/2025',
            'registration_date' => now()->subMonths(9)->format('Y-m-d'),
            'status' => 'verified',
            'total_fees' => 250000.00,
            'amount_paid' => 250000.00,
            'balance' => 0.00,
            'fees_verified' => true,
            'insurance_verified' => true,
            'verification_date' => now()->subMonths(8)->format('Y-m-d'),
            'verified_by' => 1,
        ]);
        
        // Create insurance for current semester
        StudentInsurance::create([
            'student_id' => $student->id,
            'semester_code' => '2025/2026-1',
            'academic_year' => '2025/2026',
            'provider' => 'NHI',
            'policy_number' => 'INS-' . str_pad($student->id, 8, '0', STR_PAD_LEFT),
            'expiry_date' => now()->addMonths(6)->format('Y-m-d'),
            'document_path' => 'insurance_documents/sample_' . $student->id . '.pdf',
            'submission_date' => now()->subMonths(2)->format('Y-m-d'),
            'status' => 'verified',
            'verification_date' => now()->subMonths(2)->format('Y-m-d'),
            'verified_by' => 1,
        ]);
        
        // Create invoices and payments
        $invoice = Invoice::create([
            'student_id' => $student->id,
            'semester' => '2025/2026-1',
            'amount' => 250000.00,
            'due_date' => now()->addMonth(),
            'status' => 'partial',
        ]);
        
        // Make partial payment
        Payment::create([
            'invoice_id' => $invoice->id,
            'student_id' => $student->id,
            'amount' => 150000.00,
            'payment_method' => 'bank_transfer',
            'payment_date' => now()->subWeeks(2),
            'reference_number' => 'PAY-' . str_pad($student->id, 8, '0', STR_PAD_LEFT),
            'status' => 'completed',
        ]);
    }
    
    /**
     * Seed enrollment confirmation data
     */
    private function seedEnrollmentConfirmation(Student $student): void
    {
        // Get student's current enrollments
        $enrollments = Enrollment::where('student_id', $student->id)
            ->where('semester', '2025/2026-1')
            ->with('course')
            ->get();
        
        if ($enrollments->isEmpty()) {
            return;
        }
        
        $totalUnits = $enrollments->sum(function ($enrollment) {
            return $enrollment->course->credit_hours ?? 3;
        });
        
        $confirmation = EnrollmentConfirmation::create([
            'student_id' => $student->id,
            'semester_code' => '2025/2026-1',
            'confirmation_date' => now()->subMonths(2),
            'total_units' => $totalUnits,
            'timetable_understood' => true,
            'attendance_policy_agreed' => true,
            'academic_calendar_checked' => true,
            'confirmation_email_sent' => true,
            'email_sent_at' => now()->subMonths(2),
        ]);
        
        // Add confirmation courses
        foreach ($enrollments as $enrollment) {
            EnrollmentConfirmationCourse::create([
                'enrollment_confirmation_id' => $confirmation->id,
                'course_id' => $enrollment->course_id,
                'prerequisites_met' => true,
                'no_schedule_conflict' => true,
            ]);
        }
    }
    
    /**
     * Seed enhanced academics data (CA & Exams)
     */
    private function seedAcademicsData(Student $student): void
    {
        $enrollments = Enrollment::where('student_id', $student->id)
            ->where('semester', '2025/2026-1')
            ->get();
        
        foreach ($enrollments as $enrollment) {
            // Create CA records (various types)
            $caTypes = [
                ['type' => 'quiz', 'max' => 10, 'weight' => 10],
                ['type' => 'assignment', 'max' => 20, 'weight' => 15],
                ['type' => 'midterm', 'max' => 30, 'weight' => 15],
            ];
            
            foreach ($caTypes as $caType) {
                ContinuousAssessment::create([
                    'student_id' => $student->id,
                    'course_id' => $enrollment->course_id,
                    'semester_code' => '2025/2026-1',
                    'assessment_type' => $caType['type'],
                    'assessment_name' => ucfirst($caType['type']) . ' 1',
                    'score' => rand(60, 100) / 100 * $caType['max'],
                    'max_score' => $caType['max'],
                    'weight_percentage' => $caType['weight'],
                    'assessment_date' => now()->subMonths(rand(1, 2)),
                ]);
            }
            
            // Create final exam record
            $examScore = rand(40, 95);
            FinalExam::create([
                'student_id' => $student->id,
                'course_id' => $enrollment->course_id,
                'semester_code' => '2025/2026-1',
                'exam_score' => $examScore,
                'max_score' => 100,
                'weight_percentage' => 60,
                'exam_date' => now()->subMonth(),
                'grade' => $this->calculateGrade($examScore),
            ]);
        }
        
        // Create semester summary
        $gpa = rand(250, 500) / 100; // 2.5 to 5.0
        SemesterSummary::create([
            'student_id' => $student->id,
            'semester_code' => '2025/2026-1',
            'total_units_registered' => $enrollments->count() * 3,
            'total_units_passed' => $enrollments->count() * 3,
            'semester_gpa' => $gpa,
            'cumulative_gpa' => $gpa,
            'academic_standing' => $gpa >= 3.5 ? 'excellent' : ($gpa >= 3.0 ? 'good' : 'satisfactory'),
        ]);
    }
    
    /**
     * Seed accommodation data
     */
    private function seedAccommodationData(Student $student): void
    {
        $hostels = ['Sunrise Hall', 'Sunset Hall', 'Excellence Hall'];
        $hostel = $hostels[array_rand($hostels)];
        
        $accommodation = StudentAccommodation::create([
            'student_id' => $student->id,
            'academic_year' => '2025/2026',
            'hostel_name' => $hostel,
            'room_number' => rand(100, 499),
            'bed_space' => ['A', 'B', 'C', 'D'][rand(0, 3)],
            'room_type' => 'double',
            'allocation_date' => now()->subMonths(4),
            'check_in_date' => now()->subMonths(3),
            'renewal_date' => now()->addMonths(4),
            'status' => 'active',
        ]);
        
        // Add roommate
        AccommodationRoommate::create([
            'accommodation_id' => $accommodation->id,
            'roommate_student_id' => null,
            'roommate_name' => fake()->name(),
            'roommate_phone' => fake()->phoneNumber(),
            'roommate_email' => fake()->safeEmail(),
            'bed_space' => $accommodation->bed_space === 'A' ? 'B' : 'A',
        ]);
        
        // Create accommodation fee
        AccommodationFee::create([
            'student_id' => $student->id,
            'academic_year' => '2025/2026',
            'fee_amount' => 100000.00,
            'amount_paid' => 60000.00,
            'balance' => 40000.00,
            'due_date' => now()->addMonths(2),
            'payment_status' => 'partial',
        ]);
    }
    
    /**
     * Seed hostel amenities
     */
    private function seedHostelAmenities(): void
    {
        $hostels = ['Sunrise Hall', 'Sunset Hall', 'Excellence Hall', 'Unity Hall', 'Peace Hall'];
        $amenities = [
            ['name' => 'Wi-Fi', 'description' => 'High-speed wireless internet throughout the building'],
            ['name' => 'Study Room', 'description' => '24/7 air-conditioned study space with desks and charging points'],
            ['name' => 'Laundry', 'description' => 'Coin-operated washing machines and dryers'],
            ['name' => 'Common Kitchen', 'description' => 'Shared cooking facilities with refrigerators and microwaves'],
            ['name' => '24/7 Security', 'description' => 'Round-the-clock security personnel and CCTV monitoring'],
            ['name' => 'Parking', 'description' => 'Designated parking spaces for residents'],
        ];
        
        foreach ($hostels as $hostel) {
            foreach ($amenities as $amenity) {
                AccommodationAmenity::firstOrCreate([
                    'hostel_name' => $hostel,
                    'amenity_name' => $amenity['name'],
                ], [
                    'description' => $amenity['description'],
                    'is_available' => true,
                ]);
            }
        }
    }
    
    /**
     * Seed feedback/support tickets
     */
    private function seedFeedbackData(Student $student): void
    {
        // Create 2-3 feedback tickets
        $ticketCount = rand(2, 3);
        
        for ($i = 0; $i < $ticketCount; $i++) {
            $categories = ['academic', 'accommodation', 'fees', 'portal', 'general'];
            $priorities = ['low', 'medium', 'high'];
            $statuses = ['submitted', 'in_review', 'in_progress', 'resolved'];
            
            $submittedAt = now()->subDays(rand(5, 60));
            $status = $statuses[array_rand($statuses)];
            
            $feedback = StudentFeedback::create([
                'student_id' => $student->id,
                'ticket_number' => 'FB-' . date('Y') . '-' . str_pad(StudentFeedback::count() + 1, 4, '0', STR_PAD_LEFT),
                'category' => $categories[array_rand($categories)],
                'priority' => $priorities[array_rand($priorities)],
                'subject' => fake()->sentence(),
                'message' => fake()->paragraph(2),
                'status' => $status,
                'submitted_at' => $submittedAt,
                'last_updated_at' => $submittedAt,
                'resolved_at' => $status === 'resolved' ? now()->subDays(rand(1, 5)) : null,
                'student_viewed_response' => $status === 'resolved' ? true : rand(0, 1),
            ]);
            
            // Add response if not just submitted
            if ($status !== 'submitted') {
                FeedbackResponse::create([
                    'feedback_id' => $feedback->id,
                    'responded_by' => 1, // Admin
                    'response_message' => fake()->paragraph(),
                    'is_internal_note' => false,
                    'responded_at' => $submittedAt->addDays(1),
                ]);
            }
        }
    }
    
    /**
     * Calculate grade from score
     */
    private function calculateGrade($score): string
    {
        if ($score >= 70) return 'A';
        if ($score >= 60) return 'B';
        if ($score >= 50) return 'C';
        if ($score >= 45) return 'D';
        if ($score >= 40) return 'E';
        return 'F';
    }
}

