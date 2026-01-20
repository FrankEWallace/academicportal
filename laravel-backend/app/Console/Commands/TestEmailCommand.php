<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\EmailService;
use Illuminate\Console\Command;

class TestEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:test {user_id=1} {--type=welcome}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test email sending functionality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');
        $type = $this->option('type');

        $user = User::find($userId);

        if (!$user) {
            $this->error("User with ID {$userId} not found!");
            return Command::FAILURE;
        }

        $this->info("Testing {$type} email for: {$user->name} ({$user->email})");

        $emailService = new EmailService();

        try {
            switch ($type) {
                case 'welcome':
                    $emailService->sendWelcomeEmail($user, 'TempPassword123!');
                    break;
                
                case 'reset':
                    $emailService->sendPasswordResetEmail($user, 'test-token-12345');
                    break;
                
                case 'verification':
                    $emailService->sendEmailVerification($user);
                    break;
                
                case 'grade':
                    $emailService->sendGradePublishedEmail($user, 'Introduction to Programming', 'CS101', 'A');
                    break;
                
                case 'payment-confirmation':
                    $emailService->sendPaymentConfirmationEmail($user, 5000.00, 'Credit Card', 'TXN-12345', 'Tuition Fee');
                    break;
                
                case 'payment-reminder':
                    $emailService->sendPaymentReminderEmail($user, 3000.00, 'February 28, 2026', 'Library Fee');
                    break;
                
                case 'announcement':
                    $emailService->sendAnnouncementEmail(
                        $user->email,
                        'Important System Update',
                        'The system will undergo maintenance on Saturday, January 25, 2026 from 2:00 AM to 6:00 AM. Please save your work before this time.',
                        'high',
                        'http://localhost:5173/announcements',
                        'View Details'
                    );
                    break;
                
                default:
                    $this->error("Unknown email type: {$type}");
                    return Command::FAILURE;
            }

            $this->info("✅ Email queued successfully!");
            $this->info("Check your mail log or inbox (if configured)");
            
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Failed to send email: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
