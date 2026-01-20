# 📧 Email System Quick Start Guide

## 🚀 Getting Started

### 1. Test Email System (Development)

The system is configured to use the `log` mailer by default, which writes emails to `storage/logs/laravel.log`.

```bash
# Test different email types
php artisan email:test 1 --type=welcome
php artisan email:test 1 --type=grade
php artisan email:test 1 --type=payment-confirmation
php artisan email:test 1 --type=payment-reminder
php artisan email:test 1 --type=reset
php artisan email:test 1 --type=verification
php artisan email:test 1 --type=announcement

# Process the email queue
php artisan queue:work --once

# View emails in log
tail -f storage/logs/laravel.log
```

### 2. Configure Real Email (Mailtrap for Testing)

1. **Sign up for Mailtrap** (free): https://mailtrap.io/
2. **Get SMTP credentials** from your inbox
3. **Update `.env`**:
   ```env
   MAIL_MAILER=smtp
   MAIL_HOST=sandbox.smtp.mailtrap.io
   MAIL_PORT=2525
   MAIL_USERNAME=your_mailtrap_username
   MAIL_PASSWORD=your_mailtrap_password
   MAIL_ENCRYPTION=tls
   ```
4. **Clear config cache**:
   ```bash
   php artisan config:clear
   ```
5. **Test**: Emails will now appear in your Mailtrap inbox!

### 3. Production Email Setup

#### Option A: Gmail (Good for small projects)
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your.email@gmail.com
MAIL_PASSWORD=your_app_password  # Generate at: myaccount.google.com/apppasswords
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
```

#### Option B: SendGrid (Recommended for production)
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your_sendgrid_api_key
MAIL_ENCRYPTION=tls
```

#### Option C: AWS SES (Best for large scale)
```env
MAIL_MAILER=ses
AWS_ACCESS_KEY_ID=your_key
AWS_SECRET_ACCESS_KEY=your_secret
AWS_DEFAULT_REGION=us-east-1
MAIL_FROM_ADDRESS=noreply@yourdomain.com
```

---

## 💻 Using Emails in Your Code

### Send Welcome Email to New User
```php
use App\Services\EmailService;

$emailService = new EmailService();
$emailService->sendWelcomeEmail($user, 'TempPassword123!');
```

### Send Grade Notification
```php
use App\Notifications\GradePublishedNotification;

$student->notify(new GradePublishedNotification(
    'Introduction to Programming',  // Course name
    'CS101',                          // Course code
    'A'                               // Grade (optional)
));
```

### Send Payment Confirmation
```php
use App\Services\EmailService;

$emailService = new EmailService();
$emailService->sendPaymentConfirmationEmail(
    $student,
    5000.00,           // Amount
    'Credit Card',     // Payment method
    'TXN-12345',       // Transaction ID
    'Tuition Fee'      // Payment description
);
```

### Send Payment Reminder
```php
$emailService->sendPaymentReminderEmail(
    $student,
    3000.00,                // Amount due
    'February 28, 2026',    // Due date
    'Library Fee'           // Fee type
);
```

### Send Password Reset
```php
$emailService->sendPasswordResetEmail($user, $resetToken);
```

### Send Email Verification
```php
$emailService->sendEmailVerification($user);
```

### Send System Announcement
```php
$emailService->sendAnnouncementEmail(
    'all-students@academic-nexus.com',  // Recipients
    'System Maintenance Notice',         // Title
    'The system will be down...',        // Message
    'high',                              // Priority (normal, high, urgent)
    'http://localhost:5173/updates',     // Action URL (optional)
    'View Details'                       // Action text (optional)
);
```

### Send Bulk Emails
```php
$recipients = ['user1@example.com', 'user2@example.com'];
$emailService->sendBulkEmails($recipients, new WelcomeMail($user));
```

---

## 🔔 Notification Center Integration

### Add to Your Header Component

```tsx
// In your main layout or header component
import { NotificationCenter } from '@/components/NotificationCenter';

export function Header() {
  return (
    <header className="flex items-center justify-between p-4">
      <h1>Academic Nexus Portal</h1>
      
      <div className="flex items-center gap-4">
        <NotificationCenter />
        <UserMenu />
      </div>
    </header>
  );
}
```

### Add Notification Settings Route

```tsx
// In your App.tsx or router configuration
import NotificationSettings from '@/pages/NotificationSettings';

// Add this route
{
  path: '/settings/notifications',
  element: <NotificationSettings />,
}
```

---

## ⚡ Queue Worker Setup

### Development
```bash
# Run queue worker manually
php artisan queue:work

# Or process jobs one at a time
php artisan queue:work --once
```

### Production (with Supervisor)

1. **Install Supervisor** (Linux):
   ```bash
   sudo apt-get install supervisor
   ```

2. **Create config** at `/etc/supervisor/conf.d/academic-nexus-worker.conf`:
   ```ini
   [program:academic-nexus-worker]
   process_name=%(program_name)s_%(process_num)02d
   command=php /var/www/academic-nexus-portal/artisan queue:work --sleep=3 --tries=3 --daemon
   autostart=true
   autorestart=true
   user=www-data
   numprocs=2
   redirect_stderr=true
   stdout_logfile=/var/log/academic-nexus-worker.log
   ```

3. **Start supervisor**:
   ```bash
   sudo supervisorctl reread
   sudo supervisorctl update
   sudo supervisorctl start academic-nexus-worker:*
   ```

---

## 🧪 Testing Checklist

- [ ] Test all 7 email types
- [ ] Verify email templates render correctly
- [ ] Check notification center displays notifications
- [ ] Test notification preferences save correctly
- [ ] Verify queue processing works
- [ ] Check emails are queued (not sent synchronously)
- [ ] Test with real email provider (Mailtrap)
- [ ] Verify email delivery in production

---

## 🐛 Troubleshooting

### Emails not sending?
1. Check `storage/logs/laravel.log` for errors
2. Verify `.env` email settings
3. Clear config cache: `php artisan config:clear`
4. Check queue worker is running: `php artisan queue:work`

### Queue jobs failing?
```bash
# View failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# Clear failed jobs
php artisan queue:flush
```

### Emails going to spam?
1. Configure SPF record: `v=spf1 include:_spf.sendgrid.net ~all`
2. Configure DKIM signing (via your email provider)
3. Add DMARC policy: `v=DMARC1; p=none; rua=mailto:dmarc@yourdomain.com`
4. Use a professional "from" address: `noreply@yourdomain.com`

---

## 📊 Email Analytics (Optional Future Enhancement)

You can track email opens and clicks by:
1. Using SendGrid's event webhook
2. Adding tracking pixels to email templates
3. Using URL parameters for click tracking
4. Storing email events in database

---

## ✅ Next Steps

1. ✅ Email system is ready!
2. ⏭️ Integrate email sending in existing features:
   - User registration → Send welcome email
   - Grade publishing → Send grade notification
   - Payment received → Send confirmation
   - Password reset → Send reset email
3. ⏭️ Add NotificationCenter to your header
4. ⏭️ Configure production email provider
5. ⏭️ Set up queue worker in production

---

**Last Updated:** January 20, 2026  
**Status:** ✅ Ready for Integration
