# Backup System Quick Start Guide

## 🚀 Quick Start (5 Minutes)

### 1. Create Your First Backup

```bash
# Navigate to project directory
cd /Applications/MAMP/htdocs/academic-nexus-portal

# Create a backup
php artisan backup:create

# Create a backup with email notification
php artisan backup:create --notify

# Process the email queue
php artisan queue:work --once
```

**Expected Result**: Backup created in `storage/app/backups/backup_YYYY-MM-DD_HHmmss.zip`

---

## 📋 Common Operations

### List All Backups
```bash
# Using CLI
ls -lh storage/app/backups/

# Using Tinker
php artisan tinker
>>> app(\App\Services\BackupService::class)->getAllBackups();
```

### Get Backup Statistics
```bash
php artisan tinker
>>> app(\App\Services\BackupService::class)->getBackupStats();
```

### Verify Backup Integrity
```bash
php artisan tinker
>>> app(\App\Services\BackupService::class)->verifyBackup('backup_2026-01-21_183022.zip');
```

### Restore from Backup
```bash
# Interactive restore (with confirmations)
php artisan backup:restore backup_2026-01-21_183022.zip

# You'll see:
⚠️  WARNING: This will replace all current data!

Are you sure you want to restore from this backup? (yes/no) [no]:
> yes

This action cannot be undone. Continue? (yes/no) [no]:
> yes

🔄 Verifying backup...
🔄 Restoring database...
✅ Database restored successfully!
```

### Clean Old Backups
```bash
# Delete backups older than 30 days (default)
php artisan backup:clean

# Delete backups older than 7 days
php artisan backup:clean --days=7
```

---

## 🌐 API Usage

### Authentication
All backup endpoints require admin authentication. Include your bearer token:

```bash
Authorization: Bearer YOUR_ADMIN_TOKEN
```

### List All Backups
```bash
curl -X GET http://localhost:8888/api/admin/backups \
  -H "Authorization: Bearer YOUR_TOKEN"
```

**Response:**
```json
{
  "success": true,
  "message": "Backups retrieved successfully",
  "data": {
    "backups": [
      {
        "name": "backup_2026-01-21_183022",
        "size_human": "19.74 KB",
        "created_at": "2026-01-21 18:30:22",
        "age_days": 0
      }
    ],
    "stats": {
      "total_backups": 1,
      "total_size_human": "19.74 KB"
    }
  }
}
```

### Create Backup
```bash
curl -X POST http://localhost:8888/api/admin/backups \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json"
```

### Download Backup
```bash
curl -X GET http://localhost:8888/api/admin/backups/backup_2026-01-21_183022.zip/download \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -o my-backup.zip
```

### Restore Backup
```bash
curl -X POST http://localhost:8888/api/admin/backups/backup_2026-01-21_183022.zip/restore \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Delete Backup
```bash
curl -X DELETE http://localhost:8888/api/admin/backups/backup_2026-01-21_183022.zip \
  -H "Authorization: Bearer YOUR_TOKEN"
```

---

## 🎨 Frontend Usage

### Access Admin Dashboard
1. Login as admin user
2. Navigate to `/admin/backups`
3. View backup statistics and list

### Create Backup
- Click **"Create Backup"** button in top-right corner
- Wait for success notification
- Backup appears in table automatically

### Download Backup
- Click download icon (⬇️) in Actions column
- Backup ZIP file downloads to your computer

### Restore Backup
- Click restore icon (↻) in Actions column
- Confirm first warning dialog
- Confirm second "cannot be undone" dialog
- Database restores and page refreshes

### Delete Backup
- Click delete icon (🗑️) in Actions column
- Confirm deletion dialog
- Backup removed from list

---

## ⚙️ Automated Backups Setup

### Enable Task Scheduling

1. **Update `app/Console/Kernel.php`:**

```php
protected function schedule(Schedule $schedule)
{
    // Daily backup at 2:00 AM with email notification
    $schedule->command('backup:create --notify')
             ->daily()
             ->at('02:00')
             ->name('daily-backup')
             ->withoutOverlapping();
    
    // Weekly cleanup on Sundays at 3:00 AM
    $schedule->command('backup:clean')
             ->weekly()
             ->sundays()
             ->at('03:00')
             ->name('weekly-cleanup');
}
```

2. **Add Cron Job** (Production):

```bash
# Edit crontab
crontab -e

# Add this line
* * * * * cd /Applications/MAMP/htdocs/academic-nexus-portal && php artisan schedule:run >> /dev/null 2>&1
```

3. **Test Schedule** (Development):

```bash
# Run scheduler manually
php artisan schedule:run

# See scheduled tasks
php artisan schedule:list
```

---

## 🔧 Configuration Options

### Customize Retention Period

Edit `app/Services/BackupService.php`:

```php
public function cleanOldBackups(int $days = 30): int // Change default here
{
    // ...
}
```

Or use command option:
```bash
php artisan backup:clean --days=14  # Keep only 14 days
```

### Customize Backup Location

Edit `app/Services/BackupService.php`:

```php
protected function getBackupPath(string $filename = ''): string
{
    return storage_path('app/backups/' . $filename); // Change 'backups' to your preferred folder
}
```

### Add More Files to Backup

Edit `backupFiles()` method in `BackupService.php`:

```php
protected function backupFiles(string $backupDir): void
{
    $filesDir = $backupDir . '/files';
    File::makeDirectory($filesDir, 0755, true);
    
    // Add more directories
    if (File::exists(storage_path('app/public'))) {
        File::copyDirectory(storage_path('app/public'), $filesDir . '/storage');
    }
    
    if (File::exists(base_path('.env'))) {
        File::copy(base_path('.env'), $filesDir . '/.env');
    }
    
    // Add custom directory
    if (File::exists(public_path('uploads'))) {
        File::copyDirectory(public_path('uploads'), $filesDir . '/uploads');
    }
}
```

---

## 🐛 Troubleshooting

### Backup Creation Fails

**Issue**: `mysqldump command not found`

**Solution**: Verify MAMP MySQL installation:
```bash
# Check if mysqldump exists
ls -l /Applications/MAMP/Library/bin/mysql80/bin/mysqldump

# Test mysqldump
/Applications/MAMP/Library/bin/mysql80/bin/mysqldump --version
```

### Permission Errors

**Issue**: `Permission denied` when creating backup

**Solution**: Fix storage permissions:
```bash
chmod -R 775 storage/app/backups
chown -R $(whoami):staff storage/app/backups
```

### Email Notifications Not Sent

**Issue**: Backup created but no emails received

**Solution**: 
1. Check queue configuration in `.env`:
   ```
   QUEUE_CONNECTION=database
   ```

2. Process the queue:
   ```bash
   php artisan queue:work --once
   ```

3. Check failed jobs:
   ```bash
   php artisan tinker
   >>> DB::table('failed_jobs')->get();
   ```

### Restore Fails

**Issue**: `Failed to restore database`

**Solution**: Verify backup integrity first:
```bash
# Extract manually and check
unzip -l storage/app/backups/backup_2026-01-21_183022.zip

# Should see:
database.sql
files/.env
files/storage/...
```

---

## 📊 Performance Tips

### Large Databases

For databases larger than 100MB:

1. **Increase PHP memory limit** in `.env`:
   ```
   PHP_MEMORY_LIMIT=512M
   ```

2. **Use compression** (already enabled):
   - ZIP compression reduces size by ~70%
   - 100MB database → ~30MB backup

3. **Run backups during off-peak hours**:
   ```php
   $schedule->command('backup:create')->dailyAt('02:00'); // 2 AM
   ```

### Multiple Backups

Keep only necessary backups:

```bash
# Daily: Keep last 7 days
php artisan backup:clean --days=7

# Weekly: Keep last 4 weeks
php artisan backup:clean --days=28

# Monthly: Keep last 6 months
php artisan backup:clean --days=180
```

---

## 🔒 Security Best Practices

### Production Recommendations

1. **Move backups outside web root**:
   ```php
   // Don't store in public_html
   return storage_path('backups/' . $filename); // Outside web root
   ```

2. **Encrypt backups** (future enhancement):
   ```bash
   # Manual encryption (temporary solution)
   openssl enc -aes-256-cbc -salt -in backup.zip -out backup.zip.enc
   ```

3. **Store backups off-site**:
   - AWS S3
   - Google Cloud Storage
   - Dedicated backup server
   - FTP/SFTP server

4. **Restrict API access**:
   - Already admin-only
   - Consider IP whitelisting for restore endpoint
   - Log all backup operations

5. **Secure .env in backups**:
   - Backups contain sensitive .env file
   - Never share backups publicly
   - Delete old backups securely

---

## 📈 Monitoring

### Check Backup Health

```bash
# List recent backups
php artisan tinker
>>> app(\App\Services\BackupService::class)->getAllBackups();

# Check statistics
>>> app(\App\Services\BackupService::class)->getBackupStats();

# Verify latest backup
>>> $backups = app(\App\Services\BackupService::class)->getAllBackups();
>>> $latest = $backups[0]['name'];
>>> app(\App\Services\BackupService::class)->verifyBackup($latest);
```

### Email Alerts

Enable notifications for all automated backups:

```php
// In Kernel.php
$schedule->command('backup:create --notify') // Add --notify flag
         ->daily()
         ->at('02:00');
```

### Log Monitoring

Check backup logs:
```bash
tail -f storage/logs/laravel.log | grep -i backup
```

---

## 🎯 Quick Reference

| Task | Command | Time |
|------|---------|------|
| Create backup | `php artisan backup:create` | ~2-3s |
| Create with email | `php artisan backup:create --notify` | ~2-3s |
| List backups | `ls storage/app/backups/` | Instant |
| Clean old backups | `php artisan backup:clean` | <1s |
| Restore backup | `php artisan backup:restore FILENAME` | ~3-4s |
| Verify backup | Via Tinker (see above) | <1s |

---

## 💡 Pro Tips

1. **Always verify before restore**:
   ```bash
   php artisan tinker
   >>> app(\App\Services\BackupService::class)->verifyBackup('backup.zip');
   ```

2. **Create backup before major changes**:
   ```bash
   php artisan backup:create --notify
   # Make changes
   # If something breaks, restore immediately
   ```

3. **Test restore process regularly**:
   - Create test database
   - Practice restore procedure
   - Ensure team knows how to restore

4. **Monitor backup age**:
   - Latest backup should be < 24 hours old
   - Set up alerts if no backup in 48 hours

5. **Document custom configurations**:
   - Note any changes to retention policy
   - Document additional files backed up
   - Keep backup encryption keys secure

---

## 📞 Quick Help

**Backup not working?**
1. Check `storage/logs/laravel.log`
2. Verify MAMP MySQL is running
3. Check storage permissions

**Need to restore urgently?**
```bash
php artisan backup:restore LATEST_BACKUP.zip
# Answer 'yes' to both confirmations
```

**Want to download all backups?**
```bash
cd storage/app/backups
zip -r all-backups.zip *.zip
```

---

**Last Updated**: January 21, 2026  
**Version**: 1.0  
**Need More Help?** See `WEEK2_BACKUP_SYSTEM_SUMMARY.md` for detailed documentation
