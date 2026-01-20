<?php

namespace App\Notifications;

use App\Mail\PaymentReminderMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentDueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public float $amountDue,
        public string $dueDate,
        public string $feeType
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
        return new PaymentReminderMail(
            $notifiable,
            $this->amountDue,
            $this->dueDate,
            $this->feeType
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
            'type' => 'payment_due',
            'title' => 'Payment Reminder',
            'message' => "You have a payment of $" . number_format($this->amountDue, 2) . " due on {$this->dueDate}.",
            'amount_due' => $this->amountDue,
            'due_date' => $this->dueDate,
            'fee_type' => $this->feeType,
            'action_url' => config('app.frontend_url') . '/student/fees',
        ];
    }
}
