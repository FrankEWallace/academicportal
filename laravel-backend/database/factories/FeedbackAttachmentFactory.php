<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FeedbackAttachment>
 */
class FeedbackAttachmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fileTypes = ['pdf', 'jpg', 'png', 'doc', 'docx'];
        $fileType = fake()->randomElement($fileTypes);
        
        return [
            'feedback_id' => \App\Models\StudentFeedback::factory(),
            'file_path' => 'feedback_attachments/' . fake()->uuid() . '.' . $fileType,
            'file_name' => fake()->words(3, true) . '.' . $fileType,
            'file_size' => fake()->numberBetween(50000, 5000000),
            'mime_type' => $fileType === 'pdf' ? 'application/pdf' : ($fileType === 'doc' || $fileType === 'docx' ? 'application/msword' : 'image/' . $fileType),
            'uploaded_at' => fake()->dateTimeBetween('-2 months', 'now'),
        ];
    }
}
