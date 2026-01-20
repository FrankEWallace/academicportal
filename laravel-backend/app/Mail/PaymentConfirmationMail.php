<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentConfirmationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public User $student,
        public float $amount,
        public string $paymentMethod,
        public string $transactionId,
        public string $paymentFor
    ) {
        //
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payment Confirmation - Academic Nexus Portal',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.payment-confirmation',
            with: [
                'studentName' => $this->student->name,
                'amount' => number_format($this->amount, 2),
                'paymentMethod' => $this->paymentMethod,
                'transactionId' => $this->transactionId,
                'paymentFor' => $this->paymentFor,
                'date' => now()->format('M d, Y h:i A'),
                'viewUrl' => config('app.frontend_url') . '/student/fees',
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
