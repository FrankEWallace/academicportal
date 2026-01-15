<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\StudentInsurance>
 */
class StudentInsuranceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $providers = ['National Health Insurance', 'Student Care Insurance', 'UniCover Insurance', 'Campus Health Plus'];
        $semesterCodes = ['2024/2025-1', '2024/2025-2', '2025/2026-1', '2025/2026-2'];
        
        $uploadDate = fake()->dateTimeBetween('-6 months', 'now');
        
        return [
            'student_id' => \App\Models\Student::factory(),
            'semester_code' => fake()->randomElement($semesterCodes),
            'insurance_provider' => fake()->randomElement($providers),
            'policy_number' => 'INS-' . fake()->numerify('########'),
            'expiry_date' => fake()->dateTimeBetween('now', '+1 year'),
            'document_path' => 'insurance_documents/' . fake()->uuid() . '.pdf',
            'uploaded_at' => $uploadDate,
            'verified' => fake()->boolean(80),
            'verified_by' => fake()->boolean(80) ? \App\Models\User::factory() : null,
            'verified_at' => fake()->boolean(80) ? fake()->dateTimeBetween($uploadDate, 'now') : null,
        ];
    }
}
