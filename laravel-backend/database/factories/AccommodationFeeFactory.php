<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AccommodationFee>
 */
class AccommodationFeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amount = fake()->randomFloat(2, 50000, 150000);
        $amountPaid = fake()->randomFloat(2, 0, $amount);
        
        return [
            'student_id' => \App\Models\Student::factory(),
            'academic_year' => '2025/2026',
            'fee_amount' => $amount,
            'amount_paid' => $amountPaid,
            'balance' => $amount - $amountPaid,
            'due_date' => fake()->dateTimeBetween('now', '+3 months'),
            'payment_status' => $amountPaid >= $amount ? 'paid' : ($amountPaid > 0 ? 'partial' : 'unpaid'),
        ];
    }
}
