# Student Module Enhancement - Backend Implementation

## 🗄️ Database Schema Design

### 1. Registration & Fees Tables

#### **`registrations` Table**
```sql
CREATE TABLE registrations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id BIGINT UNSIGNED NOT NULL,
    academic_year VARCHAR(20) NOT NULL,
    semester TINYINT NOT NULL,
    status ENUM('pending', 'verified', 'completed', 'rejected') DEFAULT 'pending',
    fee_payment_status ENUM('unpaid', 'partial', 'paid') DEFAULT 'unpaid',
    insurance_status ENUM('not_required', 'required', 'submitted', 'verified', 'expired') DEFAULT 'not_required',
    insurance_required BOOLEAN DEFAULT FALSE,
    registration_date TIMESTAMP NULL,
    verified_date TIMESTAMP NULL,
    verified_by BIGINT UNSIGNED NULL,
    notes TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_student_semester (student_id, academic_year, semester),
    INDEX idx_status (status)
);
```

#### **`student_insurance` Table**
```sql
CREATE TABLE student_insurance (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id BIGINT UNSIGNED NOT NULL,
    registration_id BIGINT UNSIGNED NULL,
    policy_provider VARCHAR(255) NOT NULL,
    policy_number VARCHAR(100) NOT NULL,
    policy_document_path VARCHAR(500) NULL,
    valid_from DATE NOT NULL,
    valid_until DATE NOT NULL,
    status ENUM('submitted', 'verified', 'rejected', 'expired') DEFAULT 'submitted',
    submitted_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    verified_date TIMESTAMP NULL,
    verified_by BIGINT UNSIGNED NULL,
    rejection_reason TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (registration_id) REFERENCES registrations(id) ON DELETE SET NULL,
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_student (student_id),
    INDEX idx_expiry (valid_until)
);
```

#### **Extend `invoices` Table** (if not exists)
```sql
ALTER TABLE invoices ADD COLUMN IF NOT EXISTS invoice_pdf_path VARCHAR(500) NULL;
ALTER TABLE invoices ADD COLUMN IF NOT EXISTS payment_summary TEXT NULL;
ALTER TABLE invoices ADD COLUMN IF NOT EXISTS download_count INT DEFAULT 0;
```

---

### 2. Course Enrollment Confirmation Tables

#### **`enrollment_confirmations` Table**
```sql
CREATE TABLE enrollment_confirmations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id BIGINT UNSIGNED NOT NULL,
    academic_year VARCHAR(20) NOT NULL,
    semester TINYINT NOT NULL,
    total_courses INT DEFAULT 0,
    total_credits INT DEFAULT 0,
    confirmed BOOLEAN DEFAULT FALSE,
    confirmed_at TIMESTAMP NULL,
    confirmation_ip VARCHAR(45) NULL,
    confirmation_checkboxes JSON NULL, -- Stores checkbox states
    prerequisites_met BOOLEAN DEFAULT TRUE,
    schedule_conflicts_detected BOOLEAN DEFAULT FALSE,
    schedule_conflicts JSON NULL,
    confirmation_email_sent BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    INDEX idx_student_semester (student_id, academic_year, semester),
    INDEX idx_confirmed (confirmed)
);
```

#### **`enrollment_confirmation_courses` Table**
```sql
CREATE TABLE enrollment_confirmation_courses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    enrollment_confirmation_id BIGINT UNSIGNED NOT NULL,
    enrollment_id BIGINT UNSIGNED NOT NULL,
    course_id BIGINT UNSIGNED NOT NULL,
    confirmed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (enrollment_confirmation_id) REFERENCES enrollment_confirmations(id) ON DELETE CASCADE,
    FOREIGN KEY (enrollment_id) REFERENCES enrollments(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);
```

---

### 3. Enhanced Academics Tables

#### **`continuous_assessments` Table**
```sql
CREATE TABLE continuous_assessments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    enrollment_id BIGINT UNSIGNED NOT NULL,
    course_id BIGINT UNSIGNED NOT NULL,
    student_id BIGINT UNSIGNED NOT NULL,
    assessment_type ENUM('quiz', 'assignment', 'midterm', 'project', 'presentation', 'lab', 'other') NOT NULL,
    assessment_name VARCHAR(255) NOT NULL,
    max_score DECIMAL(5,2) NOT NULL,
    obtained_score DECIMAL(5,2) NULL,
    weight_percentage DECIMAL(5,2) DEFAULT 0,
    assessment_date DATE NULL,
    published BOOLEAN DEFAULT FALSE,
    published_at TIMESTAMP NULL,
    remarks TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (enrollment_id) REFERENCES enrollments(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    INDEX idx_student_course (student_id, course_id),
    INDEX idx_published (published)
);
```

#### **`final_exams` Table**
```sql
CREATE TABLE final_exams (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    enrollment_id BIGINT UNSIGNED NOT NULL,
    course_id BIGINT UNSIGNED NOT NULL,
    student_id BIGINT UNSIGNED NOT NULL,
    max_score DECIMAL(5,2) NOT NULL DEFAULT 70.00,
    obtained_score DECIMAL(5,2) NULL,
    exam_date DATE NULL,
    exam_hall VARCHAR(100) NULL,
    seat_number VARCHAR(50) NULL,
    published BOOLEAN DEFAULT FALSE,
    published_at TIMESTAMP NULL,
    remarks TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (enrollment_id) REFERENCES enrollments(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    INDEX idx_student_course (student_id, course_id),
    INDEX idx_published (published),
    UNIQUE KEY unique_student_course_exam (student_id, course_id, enrollment_id)
);
```

#### **`semester_summaries` Table**
```sql
CREATE TABLE semester_summaries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id BIGINT UNSIGNED NOT NULL,
    academic_year VARCHAR(20) NOT NULL,
    semester TINYINT NOT NULL,
    total_courses INT DEFAULT 0,
    total_credits INT DEFAULT 0,
    semester_gpa DECIMAL(3,2) NULL,
    credits_earned INT DEFAULT 0,
    status ENUM('in_progress', 'completed', 'on_probation', 'suspended') DEFAULT 'in_progress',
    class_rank INT NULL,
    total_students_in_class INT NULL,
    generated_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    INDEX idx_student_semester (student_id, academic_year, semester),
    UNIQUE KEY unique_student_semester (student_id, academic_year, semester)
);
```

---

### 4. Accommodation Tables

#### **`student_accommodations` Table**
```sql
CREATE TABLE student_accommodations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id BIGINT UNSIGNED NOT NULL,
    academic_year VARCHAR(20) NOT NULL,
    status ENUM('not_assigned', 'assigned', 'on_campus', 'off_campus', 'cancelled') DEFAULT 'not_assigned',
    hostel_name VARCHAR(255) NULL,
    room_number VARCHAR(50) NULL,
    floor VARCHAR(20) NULL,
    room_type ENUM('single', 'shared_2', 'shared_3', 'shared_4') NULL,
    block VARCHAR(100) NULL,
    check_in_date DATE NULL,
    check_out_date DATE NULL,
    allocation_date DATE NULL,
    allocation_letter_path VARCHAR(500) NULL,
    renewal_status ENUM('not_applicable', 'pending', 'approved', 'rejected') DEFAULT 'not_applicable',
    renewal_requested_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    INDEX idx_student_year (student_id, academic_year),
    INDEX idx_status (status),
    INDEX idx_hostel_room (hostel_name, room_number)
);
```

#### **`accommodation_roommates` Table**
```sql
CREATE TABLE accommodation_roommates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    accommodation_id BIGINT UNSIGNED NOT NULL,
    student_id BIGINT UNSIGNED NOT NULL,
    roommate_student_id BIGINT UNSIGNED NOT NULL,
    is_primary_tenant BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (accommodation_id) REFERENCES student_accommodations(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (roommate_student_id) REFERENCES students(id) ON DELETE CASCADE,
    INDEX idx_accommodation (accommodation_id),
    UNIQUE KEY unique_roommate_pair (student_id, roommate_student_id, accommodation_id)
);
```

#### **`accommodation_fees` Table**
```sql
CREATE TABLE accommodation_fees (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    accommodation_id BIGINT UNSIGNED NOT NULL,
    student_id BIGINT UNSIGNED NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    amount_paid DECIMAL(10,2) DEFAULT 0,
    balance DECIMAL(10,2) GENERATED ALWAYS AS (total_amount - amount_paid) STORED,
    due_date DATE NOT NULL,
    payment_status ENUM('unpaid', 'partial', 'paid', 'overdue') DEFAULT 'unpaid',
    invoice_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (accommodation_id) REFERENCES student_accommodations(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE SET NULL,
    INDEX idx_student (student_id),
    INDEX idx_payment_status (payment_status)
);
```

#### **`accommodation_amenities` Table**
```sql
CREATE TABLE accommodation_amenities (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    hostel_name VARCHAR(255) NOT NULL,
    amenity_name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    is_available BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_hostel (hostel_name)
);
```

---

### 5. Student Feedback Tables

#### **`student_feedback` Table**
```sql
CREATE TABLE student_feedback (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id BIGINT UNSIGNED NOT NULL,
    feedback_number VARCHAR(50) UNIQUE NOT NULL, -- FB-YYYY-NNNN
    category ENUM('academic', 'library', 'facilities', 'cafeteria', 'it_services', 'accommodation', 'sports', 'health', 'other') NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    status ENUM('submitted', 'reviewed', 'in_progress', 'responded', 'resolved', 'closed') DEFAULT 'submitted',
    assigned_to BIGINT UNSIGNED NULL,
    assigned_department VARCHAR(100) NULL,
    request_email_notification BOOLEAN DEFAULT FALSE,
    submission_ip VARCHAR(45) NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_at TIMESTAMP NULL,
    responded_at TIMESTAMP NULL,
    resolved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_student (student_id),
    INDEX idx_status (status),
    INDEX idx_category (category),
    INDEX idx_feedback_number (feedback_number)
);
```

#### **`feedback_responses` Table**
```sql
CREATE TABLE feedback_responses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    feedback_id BIGINT UNSIGNED NOT NULL,
    responder_id BIGINT UNSIGNED NOT NULL,
    responder_name VARCHAR(255) NOT NULL,
    responder_department VARCHAR(100) NULL,
    response_message TEXT NOT NULL,
    is_internal_note BOOLEAN DEFAULT FALSE,
    responded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (feedback_id) REFERENCES student_feedback(id) ON DELETE CASCADE,
    FOREIGN KEY (responder_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_feedback (feedback_id)
);
```

#### **`feedback_attachments` Table**
```sql
CREATE TABLE feedback_attachments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    feedback_id BIGINT UNSIGNED NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type VARCHAR(100) NULL,
    file_size INT NULL,
    uploaded_by BIGINT UNSIGNED NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (feedback_id) REFERENCES student_feedback(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_feedback (feedback_id)
);
```

---

## 📡 API Endpoints Specification

### 1. Registration & Fees APIs

#### **Registration Status**
```
GET /api/student/registration
Response: {
  success: true,
  data: {
    registration: {
      id: 1,
      status: "verified",
      academic_year: "2025-2026",
      semester: 1,
      fee_payment_status: "paid",
      insurance_status: "verified",
      insurance_required: true,
      registration_date: "2025-08-01",
      verified_date: "2025-08-05"
    },
    fee_summary: {
      total_amount: 4500.00,
      amount_paid: 4500.00,
      balance: 0,
      payment_status: "paid"
    },
    insurance: {
      policy_provider: "Student Health Insurance Co.",
      policy_number: "SHI-2025-STU001",
      valid_from: "2025-08-01",
      valid_until: "2026-07-31",
      status: "verified",
      days_until_expiry: 198
    }
  }
}
```

#### **Fee Invoices**
```
GET /api/student/invoices
Query params: ?academic_year=2025-2026&semester=1
Response: {
  success: true,
  data: [
    {
      id: 1,
      invoice_number: "INV-2025-001",
      amount: 4500.00,
      status: "paid",
      issued_date: "2025-07-20",
      due_date: "2025-08-15",
      paid_date: "2025-08-05",
      pdf_url: "/storage/invoices/INV-2025-001.pdf"
    }
  ]
}
```

#### **Download Invoice PDF**
```
GET /api/student/invoices/{id}/download
Response: PDF file download
```

#### **Upload Insurance Document**
```
POST /api/student/insurance/upload
Body: {
  registration_id: 1,
  policy_provider: "ABC Insurance",
  policy_number: "POL-12345",
  valid_from: "2025-08-01",
  valid_until: "2026-07-31",
  document: <file>
}
Response: {
  success: true,
  message: "Insurance document uploaded successfully"
}
```

---

### 2. Enrollment Confirmation APIs

#### **Get Enrollment Prerequisites**
```
GET /api/student/enrollment/prerequisites
Response: {
  success: true,
  data: {
    registration_verified: true,
    fees_paid: true,
    insurance_verified: true,
    eligible_to_enroll: true,
    messages: []
  }
}
```

#### **Get Selected Courses for Review**
```
GET /api/student/enrollment/review
Response: {
  success: true,
  data: {
    courses: [
      {
        id: 1,
        code: "CS301",
        name: "Data Structures",
        credits: 3,
        schedule: "MWF 09:00-10:00",
        instructor: "Dr. John Smith"
      }
    ],
    total_courses: 6,
    total_credits: 18,
    schedule_conflicts: [],
    has_conflicts: false,
    credit_limit_warning: false
  }
}
```

#### **Confirm Enrollment**
```
POST /api/student/enrollment/confirm
Body: {
  academic_year: "2025-2026",
  semester: 1,
  checkboxes: {
    reviewed: true,
    understand_final: true,
    agree_attend: true
  }
}
Response: {
  success: true,
  message: "Enrollment confirmed successfully",
  data: {
    confirmation_id: 1,
    confirmed_at: "2025-08-10 10:30:00",
    email_sent: true
  }
}
```

---

### 3. Enhanced Academics APIs

#### **Get Course Performance**
```
GET /api/student/academics/performance
Query params: ?academic_year=2025-2026&semester=1
Response: {
  success: true,
  data: {
    semester_gpa: 3.75,
    year_gpa: 3.68,
    cgpa: 3.72,
    total_credits_semester: 18,
    total_credits_year: 36,
    total_credits_overall: 72,
    courses: [
      {
        id: 1,
        code: "CS301",
        name: "Data Structures",
        instructor: "Dr. John Smith",
        credits: 3,
        continuous_assessments: [
          {
            type: "quiz",
            name: "Quiz 1",
            max_score: 10,
            obtained_score: 8,
            weight: 10,
            percentage: 80
          }
        ],
        ca_total: {
          max: 30,
          obtained: 28,
          percentage: 93.3
        },
        final_exam: {
          max: 70,
          obtained: 68,
          percentage: 97.1
        },
        overall_score: 96,
        letter_grade: "A",
        grade_points: 4.0,
        class_average: 82,
        rank_percentile: 15
      }
    ]
  }
}
```

#### **Get Historical Records**
```
GET /api/student/academics/history
Response: {
  success: true,
  data: [
    {
      academic_year: "2025-2026",
      semester: 1,
      total_courses: 6,
      total_credits: 18,
      semester_gpa: 3.75,
      status: "current"
    },
    {
      academic_year: "2024-2025",
      semester: 2,
      total_courses: 6,
      total_credits: 18,
      semester_gpa: 3.68,
      status: "completed"
    }
  ]
}
```

#### **Get Detailed Course Assessment**
```
GET /api/student/academics/course/{courseId}/detailed
Response: {
  success: true,
  data: {
    course: {...},
    continuous_assessments: [...],
    final_exam: {...},
    statistics: {
      class_average: 82,
      highest_score: 98,
      lowest_score: 45,
      student_rank: 8,
      total_students: 45
    }
  }
}
```

---

### 4. Accommodation APIs

#### **Get Accommodation Status**
```
GET /api/student/accommodation
Response: {
  success: true,
  data: {
    accommodation: {
      id: 1,
      status: "on_campus",
      hostel_name: "Oak Hall",
      room_number: "301-B",
      floor: "3rd",
      room_type: "shared_2",
      block: "North Wing",
      check_in_date: "2025-08-15",
      check_out_date: "2026-05-30",
      allocation_letter_url: "/storage/allocations/2025-001.pdf"
    },
    roommates: [
      {
        student_id: 2,
        name: "Sarah Johnson",
        program: "Computer Science - Year 2",
        email: "sarah.johnson@student.example.com"
      }
    ],
    fees: {
      total_amount: 2400.00,
      amount_paid: 2400.00,
      balance: 0,
      payment_status: "paid"
    },
    amenities: [
      "Wi-Fi",
      "Laundry",
      "Study Room",
      "Cafeteria"
    ],
    renewal: {
      current_contract_end: "2026-05-30",
      renewal_period_opens: "2026-03-01",
      status: "not_yet_available"
    }
  }
}
```

#### **Download Allocation Letter**
```
GET /api/student/accommodation/allocation-letter
Response: PDF file download
```

#### **Apply for Renewal**
```
POST /api/student/accommodation/renewal
Response: {
  success: true,
  message: "Renewal request submitted successfully"
}
```

---

### 5. Feedback APIs

#### **Submit Feedback**
```
POST /api/student/feedback
Body: {
  category: "academic",
  subject: "Course Materials Not Available",
  message: "The lecture notes for CS301...",
  priority: "medium",
  request_email_notification: true
}
Response: {
  success: true,
  message: "Feedback submitted successfully",
  data: {
    feedback_number: "FB-2026-0045",
    submitted_at: "2026-01-15 10:30:00"
  }
}
```

#### **Get Feedback History**
```
GET /api/student/feedback
Query params: ?status=all&page=1&per_page=10
Response: {
  success: true,
  data: {
    current_page: 1,
    data: [
      {
        id: 1,
        feedback_number: "FB-2026-0045",
        category: "academic",
        subject: "Course Materials Not Available",
        priority: "medium",
        status: "responded",
        submitted_at: "2026-01-10",
        has_response: true
      }
    ],
    total: 15,
    per_page: 10,
    last_page: 2
  }
}
```

#### **Get Feedback Details**
```
GET /api/student/feedback/{id}
Response: {
  success: true,
  data: {
    feedback: {
      id: 1,
      feedback_number: "FB-2026-0045",
      category: "academic",
      subject: "Course Materials Not Available",
      message: "The lecture notes...",
      priority: "medium",
      status: "responded",
      submitted_at: "2026-01-10",
      responded_at: "2026-01-12"
    },
    responses: [
      {
        id: 1,
        responder_name: "Dr. Sarah Williams",
        responder_department: "Academic Department",
        message: "Thank you for bringing this...",
        responded_at: "2026-01-12"
      }
    ]
  }
}
```

---

## 🔐 Permissions & Middleware

### **Student Permissions**
```php
'student' => [
    'registration.read',
    'insurance.upload',
    'enrollment.confirm',
    'academics.read',
    'accommodation.read',
    'accommodation.renewal',
    'feedback.create',
    'feedback.read',
]
```

### **Admin Permissions** (for managing these features)
```php
'admin' => [
    'registration.create', 'registration.verify',
    'insurance.verify', 'insurance.reject',
    'accommodation.assign', 'accommodation.manage',
    'feedback.respond', 'feedback.assign',
]
```

---

## 📁 File Storage Structure

```
storage/app/public/
├── invoices/
│   ├── 2025/
│   │   ├── INV-2025-001.pdf
│   │   └── INV-2025-002.pdf
├── insurance/
│   ├── student-{id}/
│   │   ├── policy-2025.pdf
│   │   └── policy-2026.pdf
├── accommodation/
│   ├── allocations/
│   │   ├── 2025-001.pdf
│   │   └── 2025-002.pdf
├── feedback/
│   ├── attachments/
│   │   ├── FB-2026-0045/
│   │   │   └── screenshot.png
```

---

**Next Step**: Laravel backend implementation (migrations, models, controllers, routes)
