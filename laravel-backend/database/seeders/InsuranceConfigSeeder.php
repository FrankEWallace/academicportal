<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\InsuranceConfig;
use App\Models\User;

class InsuranceConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currentYear = now()->year;
        $nextYear = $currentYear + 1;
        
        // Get an admin user to set as the updater
        $admin = User::where('role', 'admin')->first();

        InsuranceConfig::create([
            'academic_year' => "{$currentYear}/{$nextYear}",
            'requirement_level' => 'mandatory',
            'blocks_registration' => true,
            'updated_by' => $admin->id,
        ]);
    }
}
