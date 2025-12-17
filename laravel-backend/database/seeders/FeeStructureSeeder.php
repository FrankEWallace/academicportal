<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FeeStructure;

class FeeStructureSeeder extends Seeder
{
    public function run(): void
    {
        $feeStructures = [
            // Computer Science Program
            [
                'program' => 'Computer Science',
                'semester' => 1,
                'amount' => 2500.00,
                'due_date' => '2025-01-15',
                'fee_type' => 'tuition',
                'description' => 'Semester 1 tuition fees for Computer Science program',
                'status' => 'active',
            ],
            [
                'program' => 'Computer Science',
                'semester' => 1,
                'amount' => 300.00,
                'due_date' => '2025-01-15',
                'fee_type' => 'library',
                'description' => 'Library fees for Computer Science students',
                'status' => 'active',
            ],
            [
                'program' => 'Computer Science',
                'semester' => 1,
                'amount' => 500.00,
                'due_date' => '2025-01-15',
                'fee_type' => 'lab',
                'description' => 'Computer lab fees for practical sessions',
                'status' => 'active',
            ],
            [
                'program' => 'Computer Science',
                'semester' => 2,
                'amount' => 2500.00,
                'due_date' => '2025-06-15',
                'fee_type' => 'tuition',
                'description' => 'Semester 2 tuition fees for Computer Science program',
                'status' => 'active',
            ],
            [
                'program' => 'Computer Science',
                'semester' => 2,
                'amount' => 300.00,
                'due_date' => '2025-06-15',
                'fee_type' => 'library',
                'description' => 'Library fees for Computer Science students',
                'status' => 'active',
            ],
            [
                'program' => 'Computer Science',
                'semester' => 2,
                'amount' => 500.00,
                'due_date' => '2025-06-15',
                'fee_type' => 'lab',
                'description' => 'Computer lab fees for practical sessions',
                'status' => 'active',
            ],

            // Business Administration Program
            [
                'program' => 'Business Administration',
                'semester' => 1,
                'amount' => 2200.00,
                'due_date' => '2025-01-15',
                'fee_type' => 'tuition',
                'description' => 'Semester 1 tuition fees for Business Administration program',
                'status' => 'active',
            ],
            [
                'program' => 'Business Administration',
                'semester' => 1,
                'amount' => 250.00,
                'due_date' => '2025-01-15',
                'fee_type' => 'library',
                'description' => 'Library fees for Business Administration students',
                'status' => 'active',
            ],
            [
                'program' => 'Business Administration',
                'semester' => 1,
                'amount' => 200.00,
                'due_date' => '2025-01-15',
                'fee_type' => 'activity',
                'description' => 'Student activity and events fee',
                'status' => 'active',
            ],
            [
                'program' => 'Business Administration',
                'semester' => 2,
                'amount' => 2200.00,
                'due_date' => '2025-06-15',
                'fee_type' => 'tuition',
                'description' => 'Semester 2 tuition fees for Business Administration program',
                'status' => 'active',
            ],

            // Engineering Program
            [
                'program' => 'Engineering',
                'semester' => 1,
                'amount' => 2800.00,
                'due_date' => '2025-01-15',
                'fee_type' => 'tuition',
                'description' => 'Semester 1 tuition fees for Engineering program',
                'status' => 'active',
            ],
            [
                'program' => 'Engineering',
                'semester' => 1,
                'amount' => 600.00,
                'due_date' => '2025-01-15',
                'fee_type' => 'lab',
                'description' => 'Engineering lab fees for practical work',
                'status' => 'active',
            ],
            [
                'program' => 'Engineering',
                'semester' => 1,
                'amount' => 400.00,
                'due_date' => '2025-01-15',
                'fee_type' => 'workshop',
                'description' => 'Workshop and equipment usage fees',
                'status' => 'active',
            ],

            // Some overdue fees for testing
            [
                'program' => 'Computer Science',
                'semester' => 1,
                'amount' => 150.00,
                'due_date' => '2024-12-01',
                'fee_type' => 'late_fee',
                'description' => 'Late payment penalty fee',
                'status' => 'active',
            ],
            [
                'program' => 'Business Administration',
                'semester' => 1,
                'amount' => 100.00,
                'due_date' => '2024-11-30',
                'fee_type' => 'exam',
                'description' => 'Examination fees',
                'status' => 'active',
            ],
        ];

        foreach ($feeStructures as $feeStructure) {
            FeeStructure::create($feeStructure);
        }
    }
}
