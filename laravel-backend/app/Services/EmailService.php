<?php

namespace App\Services;

use App\Mail\AnnouncementMail;
use App\Mail\EmailVerificationMail;
use App\Mail\GradePublishedMail;
use App\Mail\PaymentConfirmationMail;
use App\Mail\PaymentReminderMail;
use App\Mail\PasswordResetMail;
use App\Mail\WelcomeMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class EmailService
{
    /**
     * Send welcome email to new user
     */
    public function sendWelcomeEmail(User $user, string $temporaryPassword = ''): void
    {
        Mail::to($user->email)->send(new WelcomeMail($user, $temporaryPassword));
    }

    /**
     * Send password reset email
     */
    public function sendPasswordResetEmail(User $user, string $token): void
    {
        $resetUrl = config('app.frontend_url') . '/reset-password?token=' . $token . '&email=' . urlencode($user->email);
        
        Mail::to($user->email)->send(new PasswordResetMail($user, $token, $resetUrl));
    }

    /**
     * Send email verification
     */
    public function sendEmailVerification(User $user): void
    {
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addHours(24),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        Mail::to($user->email)->send(new EmailVerificationMail($user, $verificationUrl));
    }

    /**
     * Send grade published notification
     */
    public function sendGradePublishedEmail(
        User $student,
        string $courseName,
        string $courseCode,
        ?string $grade = null
    ): void {
        Mail::to($student->email)->send(
            new GradePublishedMail($student, $courseName, $courseCode, $grade)
        );
    }

    /**
     * Send payment confirmation email
     */
    public function sendPaymentConfirmationEmail(
        User $student,
        float $amount,
        string $paymentMethod,
        string $transactionId,
        string $paymentFor
    ): void {
        Mail::to($student->email)->send(
            new PaymentConfirmationMail($student, $amount, $paymentMethod, $transactionId, $paymentFor)
        );
    }

    /**
     * Send payment reminder email
     */
    public function sendPaymentReminderEmail(
        User $student,
        float $amountDue,
        string $dueDate,
        string $feeType
    ): void {
        Mail::to($student->email)->send(
            new PaymentReminderMail($student, $amountDue, $dueDate, $feeType)
        );
    }

    /**
     * Send announcement email
     */
    public function sendAnnouncementEmail(
        $recipients,
        string $title,
        string $message,
        string $priority = 'normal',
        ?string $actionUrl = null,
        ?string $actionText = null
    ): void {
        $mailable = new AnnouncementMail($title, $message, $priority, $actionUrl, $actionText);

        if (is_array($recipients)) {
            Mail::to($recipients)->send($mailable);
        } else {
            Mail::to($recipients)->send($mailable);
        }
    }

    /**
     * Send bulk emails (queued)
     */
    public function sendBulkEmails(array $recipients, $mailable): void
    {
        foreach ($recipients as $recipient) {
            Mail::to($recipient)->queue($mailable);
        }
    }
}
