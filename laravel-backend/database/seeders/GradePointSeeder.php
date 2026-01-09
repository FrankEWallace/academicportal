<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GradePoint;

class GradePointSeeder extends Seeder
{
    public function run(): void
    {
        $gradePoints = [
            [
                'letter_grade' => 'A+',
                'min_percentage' => 97.00,
                'max_percentage' => 100.00,
                'grade_point' => 5.00,
                'description' => 'Exceptional',
                'is_passing' => true,
                'order' => 1
            ],
            [
                'letter_grade' => 'A',
                'min_percentage' => 93.00,
                'max_percentage' => 96.99,
                'grade_point' => 4.75,
                'description' => 'Excellent',
                'is_passing' => true,
                'order' => 2
            ],
            [
                'letter_grade' => 'A-',
                'min_percentage' => 90.00,
                'max_percentage' => 92.99,
                'grade_point' => 4.50,
                'description' => 'Very Good',
                'is_passing' => true,
                'order' => 3
            ],
            [
                'letter_grade' => 'B+',
                'min_percentage' => 87.00,
                'max_percentage' => 89.99,
                'grade_point' => 4.00,
                'description' => 'Good',
                'is_passing' => true,
                'order' => 4
            ],
            [
                'letter_grade' => 'B',
                'min_percentage' => 83.00,
                'max_percentage' => 86.99,
                'grade_point' => 3.50,
                'description' => 'Above Average',
                'is_passing' => true,
                'order' => 5
            ],
            [
                'letter_grade' => 'B-',
                'min_percentage' => 80.00,
                'max_percentage' => 82.99,
                'grade_point' => 3.00,
                'description' => 'Average',
                'is_passing' => true,
                'order' => 6
            ],
            [
                'letter_grade' => 'C+',
                'min_percentage' => 77.00,
                'max_percentage' => 79.99,
                'grade_point' => 2.50,
                'description' => 'Fair',
                'is_passing' => true,
                'order' => 7
            ],
            [
                'letter_grade' => 'C',
                'min_percentage' => 73.00,
                'max_percentage' => 76.99,
                'grade_point' => 2.00,
                'description' => 'Satisfactory',
                'is_passing' => true,
                'order' => 8
            ],
            [
                'letter_grade' => 'C-',
                'min_percentage' => 70.00,
                'max_percentage' => 72.99,
                'grade_point' => 1.50,
                'description' => 'Minimum Pass',
                'is_passing' => true,
                'order' => 9
            ],
            [
                'letter_grade' => 'D+',
                'min_percentage' => 67.00,
                'max_percentage' => 69.99,
                'grade_point' => 1.25,
                'description' => 'Below Average',
                'is_passing' => true,
                'order' => 10
            ],
            [
                'letter_grade' => 'D',
                'min_percentage' => 60.00,
                'max_percentage' => 66.99,
                'grade_point' => 1.00,
                'description' => 'Poor',
                'is_passing' => true,
                'order' => 11
            ],
            [
                'letter_grade' => 'F',
                'min_percentage' => 0.00,
                'max_percentage' => 59.99,
                'grade_point' => 0.00,
                'description' => 'Fail',
                'is_passing' => false,
                'order' => 12
            ],
        ];

        foreach ($gradePoints as $gradePoint) {
            GradePoint::create($gradePoint);
        }
    }
}
