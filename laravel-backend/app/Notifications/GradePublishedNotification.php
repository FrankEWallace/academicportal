<?php

namespace App\Notifications;

use App\Mail\GradePublishedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GradePublishedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public string $courseName,
        public string $courseCode,
        public ?string $grade = null
    ) {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return new GradePublishedMail(
            $notifiable,
            $this->courseName,
            $this->courseCode,
            $this->grade
        );
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'grade_published',
            'title' => 'New Grade Published',
            'message' => "Your grade for {$this->courseName} ({$this->courseCode}) has been published.",
            'course_name' => $this->courseName,
            'course_code' => $this->courseCode,
            'grade' => $this->grade,
            'action_url' => config('app.frontend_url') . '/student/results',
        ];
    }
}
