<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            [
                'name' => 'Computer Science',
                'code' => 'CSC',
                'description' => 'Department of Computer Science and Software Engineering',
                'head_teacher_id' => null, // Can be assigned later
                'established_year' => 1995,
                'budget' => 500000.00,
                'location' => 'Science Complex, Block A',
                'phone' => '+1-555-0101',
                'email' => 'cs@university.edu',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Mathematics',
                'code' => 'MAT',
                'description' => 'Department of Pure and Applied Mathematics',
                'head_teacher_id' => null,
                'established_year' => 1990,
                'budget' => 350000.00,
                'location' => 'Science Complex, Block B',
                'phone' => '+1-555-0102',
                'email' => 'math@university.edu',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Physics',
                'code' => 'PHY',
                'description' => 'Department of Physics and Astronomy',
                'head_teacher_id' => null,
                'established_year' => 1992,
                'budget' => 450000.00,
                'location' => 'Science Complex, Block C',
                'phone' => '+1-555-0103',
                'email' => 'physics@university.edu',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Chemistry',
                'code' => 'CHM',
                'description' => 'Department of Chemistry and Chemical Engineering',
                'head_teacher_id' => null,
                'established_year' => 1991,
                'budget' => 420000.00,
                'location' => 'Science Complex, Block D',
                'phone' => '+1-555-0104',
                'email' => 'chemistry@university.edu',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Biology',
                'code' => 'BIO',
                'description' => 'Department of Biological Sciences',
                'head_teacher_id' => null,
                'established_year' => 1993,
                'budget' => 380000.00,
                'location' => 'Life Sciences Building',
                'phone' => '+1-555-0105',
                'email' => 'biology@university.edu',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'English Language',
                'code' => 'ENG',
                'description' => 'Department of English Language and Literature',
                'head_teacher_id' => null,
                'established_year' => 1988,
                'budget' => 280000.00,
                'location' => 'Arts Building, Floor 1',
                'phone' => '+1-555-0106',
                'email' => 'english@university.edu',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'History',
                'code' => 'HIS',
                'description' => 'Department of History and Archaeology',
                'head_teacher_id' => null,
                'established_year' => 1987,
                'budget' => 250000.00,
                'location' => 'Arts Building, Floor 2',
                'phone' => '+1-555-0107',
                'email' => 'history@university.edu',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Economics',
                'code' => 'ECO',
                'description' => 'Department of Economics and Business Administration',
                'head_teacher_id' => null,
                'established_year' => 1994,
                'budget' => 320000.00,
                'location' => 'Business School',
                'phone' => '+1-555-0108',
                'email' => 'economics@university.edu',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Psychology',
                'code' => 'PSY',
                'description' => 'Department of Psychology and Behavioral Sciences',
                'head_teacher_id' => null,
                'established_year' => 1996,
                'budget' => 290000.00,
                'location' => 'Social Sciences Building',
                'phone' => '+1-555-0109',
                'email' => 'psychology@university.edu',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Civil Engineering',
                'code' => 'CVE',
                'description' => 'Department of Civil and Environmental Engineering',
                'head_teacher_id' => null,
                'established_year' => 1998,
                'budget' => 480000.00,
                'location' => 'Engineering Complex',
                'phone' => '+1-555-0110',
                'email' => 'civil@university.edu',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('departments')->insert($departments);
    }
}
