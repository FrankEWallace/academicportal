# Quick Setup Guide - Student Self-Service Portal Backend

##  Installation (5 Minutes)

### Step 1: Install PDF Library

```bash
cd laravel-backend
composer require barryvdh/laravel-dompdf
```

### Step 2: Publish Configuration (Optional)

```bash
php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"
```

### Step 3: Run Database Migration

```bash
php artisan migrate
```

This creates the `document_requests` table.

### Step 4: Clear Caches

```bash
php artisan config:clear
php artisan cache:clear
php artisan route:clear
```

### Step 5: Test the API

Start your Laravel server:
```bash
php artisan serve
```

Test an endpoint (you'll need a JWT token):
```bash
curl -H "Authorization: Bearer YOUR_TOKEN" \
     http://localhost:8000/api/student/documents/admission-letter
```

---

##  Verification Checklist

- [ ] DomPDF installed successfully
- [ ] Migration ran without errors
- [ ] Routes are accessible
- [ ] Can generate a PDF document
- [ ] Frontend can communicate with backend

---

## 🧪 Testing with Postman

### 1. Get JWT Token

```http
POST http://localhost:8000/api/auth/login
Content-Type: application/json

{
  "email": "student@example.com",
  "password": "password"
}
```

### 2. Test Document Generation

```http
GET http://localhost:8000/api/student/documents/admission-letter
Authorization: Bearer YOUR_JWT_TOKEN
Accept: application/pdf
```

### 3. Create Document Request

```http
POST http://localhost:8000/api/student/document-requests
Authorization: Bearer YOUR_JWT_TOKEN
Content-Type: application/json

{
  "document_type": "transcript",
  "reason": "Graduate school application",
  "additional_info": "Please certify and seal the envelope"
}
```

### 4. List Document Requests

```http
GET http://localhost:8000/api/student/document-requests
Authorization: Bearer YOUR_JWT_TOKEN
```

---

##  Troubleshooting

### Problem: "Class 'DomPDF' not found"

**Solution**:
```bash
composer dump-autoload
php artisan config:clear
```

### Problem: Migration fails

**Solution**:
```bash
php artisan migrate:fresh
# Or if you want to keep data:
php artisan migrate:rollback
php artisan migrate
```

### Problem: PDF shows blank page

**Solution**:
Check that view files exist in `resources/views/pdfs/`

### Problem: "Student profile not found"

**Solution**:
Make sure your user has an associated student record in the database.

---

##  Configuration (Optional)

### Customize PDF Settings

Edit `config/dompdf.php`:

```php
return [
    'show_warnings' => false,
    'public_path' => public_path(),
    'convert_entities' => true,
    'options' => [
        'font_dir' => storage_path('fonts/'),
        'font_cache' => storage_path('fonts/'),
        'temp_dir' => sys_get_temp_dir(),
        'chroot' => realpath(base_path()),
        'enable_font_subsetting' => false,
        'pdf_backend' => 'CPDF',
        'default_media_type' => 'screen',
        'default_paper_size' => 'a4',
        'default_font' => 'serif',
        'dpi' => 96,
        'enable_php' => false,
        'enable_javascript' => false,
        'enable_remote' => true,
        'font_height_ratio' => 1.1,
        'enable_html5_parser' => false,
    ],
];
```

---

##  Security Setup (Recommended)

### 1. Set Storage Permissions

```bash
chmod -R 775 storage
chmod -R 775 bootstrap/cache
```

### 2. Secure Document Storage

Create a non-public storage location for completed documents:

```bash
mkdir storage/app/documents
chmod 755 storage/app/documents
```

Update `.env`:
```env
FILESYSTEM_DISK=local
```

### 3. Add Rate Limiting

In `app/Http/Kernel.php`:

```php
protected $middlewareGroups = [
    'api' => [
        // ...
        'throttle:api',
    ],
];
```

In `config/sanctum.php`:
```php
'middleware' => [
    'throttle:60,1',
],
```

---

##  Database Seeder (Optional)

Create test document requests:

```bash
php artisan make:seeder DocumentRequestSeeder
```

```php
<?php

namespace Database\Seeders;

use App\Models\DocumentRequest;
use App\Models\Student;
use Illuminate\Database\Seeder;

class DocumentRequestSeeder extends Seeder
{
    public function run()
    {
        $students = Student::limit(5)->get();

        foreach ($students as $student) {
            DocumentRequest::create([
                'student_id' => $student->id,
                'document_type' => 'transcript',
                'reason' => 'Testing purposes',
                'status' => 'pending',
                'requested_at' => now(),
            ]);
        }
    }
}
```

Run it:
```bash
php artisan db:seed --class=DocumentRequestSeeder
```

---

##  Quick Test Script

Create `test-documents.sh`:

```bash
#!/bin/bash

echo "Testing Student Document APIs..."

# Set your token here
TOKEN="YOUR_JWT_TOKEN_HERE"
BASE_URL="http://localhost:8000/api/student"

# Test 1: Admission Letter
echo "\n1. Testing Admission Letter..."
curl -H "Authorization: Bearer $TOKEN" \
     "$BASE_URL/documents/admission-letter" \
     --output test-admission.pdf
echo " Saved to test-admission.pdf"

# Test 2: ID Card
echo "\n2. Testing ID Card..."
curl -H "Authorization: Bearer $TOKEN" \
     "$BASE_URL/documents/id-card" \
     --output test-id-card.pdf
echo " Saved to test-id-card.pdf"

# Test 3: Course Registration
echo "\n3. Testing Course Registration..."
curl -H "Authorization: Bearer $TOKEN" \
     "$BASE_URL/enrollment/registration-form" \
     --output test-registration.pdf
echo " Saved to test-registration.pdf"

# Test 4: Create Document Request
echo "\n4. Creating Document Request..."
curl -X POST "$BASE_URL/document-requests" \
     -H "Authorization: Bearer $TOKEN" \
     -H "Content-Type: application/json" \
     -d '{
       "document_type": "transcript",
       "reason": "Testing API",
       "additional_info": "Test request"
     }'

# Test 5: List Document Requests
echo "\n5. Listing Document Requests..."
curl -H "Authorization: Bearer $TOKEN" \
     "$BASE_URL/document-requests"

echo "\n\n All tests complete!"
```

Make it executable:
```bash
chmod +x test-documents.sh
./test-documents.sh
```

---

##  What's Included

### Controllers:
-  StudentDocumentController (document generation)
-  DocumentRequestController (request management)
-  PaymentController (receipt generation)

### Services:
-  PdfGeneratorService (centralized PDF creation)

### Models:
-  DocumentRequest (with scopes and helpers)

### Views:
-  7 professional PDF templates

### Routes:
-  12 new API endpoints

### Migration:
-  document_requests table

---

##  Next Steps

### For Development:
1. Test all endpoints with real student data
2. Verify PDF generation quality
3. Test error scenarios
4. Check access control

### For Production:
1. Install on production server
2. Run migrations
3. Set up file storage (S3/DigitalOcean Spaces)
4. Configure email notifications
5. Set up monitoring and logging
6. Implement rate limiting
7. Add digital signatures to PDFs
8. Create admin approval interface

---

##  Need Help?

- **Documentation**: See `BACKEND_INTEGRATION_COMPLETE.md`
- **Frontend**: See `STUDENT_SELF_SERVICE_PORTAL.md`
- **Quick Start**: See `STUDENT_PORTAL_QUICK_START.md`

---

**Setup Time**: ~5 minutes  
**Difficulty**: Easy  
**Prerequisites**: Laravel, MySQL, Composer  
**Status**:  Ready to use!
