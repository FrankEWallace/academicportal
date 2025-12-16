# Academic Nexus Portal API Documentation

## Overview
This document covers the Week 3 features including Assignment Management, Grade Tracking, GPA Calculation, and Attendance System.

## Base URL
```
http://127.0.0.1:8000/api
```

## Authentication
All protected endpoints require authentication via Bearer token:
```
Authorization: Bearer {token}
```

---

## Assignment Endpoints

### 1. Get Course Assignments
**GET** `/courses/{courseId}/assignments`

Get all assignments for a specific course.

**Parameters:**
- `courseId` (integer, required) - Course ID

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "title": "Midterm Project",
      "description": "Create a web application using React",
      "type": "project",
      "total_points": 100,
      "due_date": "2025-12-15T23:59:59.000000Z",
      "instructions": "Follow the provided guidelines...",
      "course": {
        "id": 3,
        "name": "Introduction to Programming",
        "code": "CS101"
      }
    }
  ]
}
```

### 2. Create Assignment
**POST** `/assignments`

Create a new assignment (teachers/admins only).

**Request Body:**
```json
{
  "course_id": 3,
  "title": "Final Project",
  "description": "Comprehensive web application",
  "type": "project",
  "total_points": 150,
  "due_date": "2025-12-20",
  "instructions": "Build a full-stack application..."
}
```

**Response:**
```json
{
  "success": true,
  "message": "Assignment created successfully",
  "data": {
    "id": 2,
    "title": "Final Project",
    "description": "Comprehensive web application",
    "type": "project",
    "total_points": 150,
    "due_date": "2025-12-20T00:00:00.000000Z",
    "course_id": 3,
    "created_by": 1,
    "created_at": "2025-12-07T10:00:00.000000Z"
  }
}
```

---

## Assignment Grades Endpoints

### 1. Submit/Update Grade
**POST** `/assignment-grades`

Submit or update a grade for an assignment.

**Request Body:**
```json
{
  "student_id": 1,
  "assignment_id": 1,
  "score": 88,
  "feedback": "Great work on the user interface design!"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Grade submitted successfully",
  "data": {
    "id": 1,
    "student_id": 1,
    "assignment_id": 1,
    "score": 88,
    "percentage": 88,
    "letter_grade": "B+",
    "feedback": "Great work on the user interface design!",
    "graded_by": 1,
    "graded_at": "2025-12-07T10:00:00.000000Z"
  }
}
```

### 2. Get Student Grades
**GET** `/students/{studentId}/assignment-grades`

Get all assignment grades for a student.

**Parameters:**
- `studentId` (integer, required) - Student ID

**Response:**
```json
{
  "success": true,
  "data": {
    "student": {
      "id": 1,
      "name": "John Doe",
      "student_id": "STU001"
    },
    "grades": [
      {
        "id": 1,
        "score": 88,
        "percentage": 88,
        "letter_grade": "B+",
        "feedback": "Great work on the user interface design!",
        "graded_at": "2025-12-07T10:00:00.000000Z",
        "assignment": {
          "id": 1,
          "title": "Midterm Project",
          "total_points": 100,
          "due_date": "2025-12-15T23:59:59.000000Z"
        }
      }
    ],
    "statistics": {
      "total_assignments": 1,
      "average_score": 88,
      "highest_score": 88,
      "lowest_score": 88
    }
  }
}
```

### 3. Get Assignment Grades
**GET** `/assignments/{assignmentId}/grades`

Get all grades for a specific assignment (teachers only).

**Response:**
```json
{
  "success": true,
  "data": {
    "assignment": {
      "id": 1,
      "title": "Midterm Project",
      "total_points": 100
    },
    "grades": [
      {
        "id": 1,
        "score": 88,
        "percentage": 88,
        "letter_grade": "B+",
        "student": {
          "id": 1,
          "name": "John Doe",
          "student_id": "STU001"
        }
      }
    ],
    "statistics": {
      "total_submissions": 1,
      "average_score": 88,
      "highest_score": 88,
      "lowest_score": 88,
      "grade_distribution": {
        "A+": 0,
        "A": 0,
        "A-": 0,
        "B+": 1,
        "B": 0,
        "B-": 0,
        "C+": 0,
        "C": 0,
        "C-": 0,
        "D": 0,
        "F": 0
      }
    }
  }
}
```

---

## GPA Calculation Endpoints

### 1. Get Student GPA
**GET** `/students/{studentId}/gpa`

Get comprehensive GPA and academic performance data for a student.

**Parameters:**
- `studentId` (integer, required) - Student ID

**Response:**
```json
{
  "success": true,
  "data": {
    "student": {
      "id": 1,
      "name": "John Doe",
      "student_id": "STU001",
      "department": "Computer Science",
      "semester": 3
    },
    "gpa": {
      "current_gpa": 3.85,
      "total_credits": 45,
      "total_grade_points": 173.25,
      "courses_completed": 15
    },
    "assignment_performance": {
      "total_assignments": 25,
      "average_score": 87.5,
      "average_percentage": 87.5,
      "highest_score": "98.00",
      "lowest_score": "72.00"
    },
    "semester_performance": [
      {
        "semester": 1,
        "courses": 5,
        "total_grade_points": 60,
        "total_credits": 15,
        "gpa": 4.0
      }
    ],
    "recent_trend": {
      "recent_course_average": 3.88,
      "recent_assignment_average": 3.52,
      "trend_direction": "improving"
    },
    "grade_distribution": {
      "A+": 5,
      "A": 7,
      "A-": 2,
      "B+": 1,
      "B": 0,
      "B-": 0,
      "C+": 0,
      "C": 0,
      "C-": 0,
      "D": 0,
      "F": 0
    }
  }
}
```

---

## Attendance Endpoints

### 1. Mark Attendance
**POST** `/attendance`

Mark attendance for a student (teachers/admins only).

**Request Body:**
```json
{
  "student_id": 1,
  "course_id": 3,
  "date": "2025-12-07",
  "status": "present",
  "notes": "Participated actively in class discussion"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Attendance marked successfully",
  "data": {
    "id": 4,
    "student_id": 1,
    "course_id": 3,
    "date": "2025-12-07",
    "status": "present",
    "notes": "Participated actively in class discussion",
    "marked_at": "2025-12-07T10:00:00.000000Z",
    "student": {
      "id": 1,
      "name": "John Doe",
      "student_id": "STU001"
    },
    "course": {
      "id": 3,
      "name": "Introduction to Programming",
      "code": "CS101"
    },
    "marked_by": {
      "id": 1,
      "name": "Dr. John Smith"
    }
  }
}
```

### 2. Get Student Attendance
**GET** `/students/{studentId}/attendance`

Get comprehensive attendance data for a student.

**Parameters:**
- `studentId` (integer, required) - Student ID

**Response:**
```json
{
  "success": true,
  "data": {
    "student": {
      "id": 1,
      "name": "John Doe",
      "student_id": "STU001"
    },
    "overall_statistics": {
      "total_records": 20,
      "present": 18,
      "absent": 1,
      "late": 1,
      "excused": 0,
      "attendance_percentage": 90.0
    },
    "course_statistics": [
      {
        "course": {
          "id": 3,
          "name": "Introduction to Programming",
          "code": "CS101"
        },
        "statistics": {
          "total_classes": 10,
          "present": 9,
          "late": 1,
          "absent": 0,
          "excused": 0,
          "attendance_percentage": 90.0
        }
      }
    ],
    "attendance_records": [
      {
        "id": 4,
        "date": "2025-12-07",
        "status": "present",
        "notes": "Participated actively in class discussion",
        "marked_at": "2025-12-07T10:00:00.000000Z",
        "course": {
          "id": 3,
          "name": "Introduction to Programming",
          "code": "CS101"
        },
        "marked_by": {
          "id": 1,
          "name": "Dr. John Smith"
        }
      }
    ]
  }
}
```

### 3. Get Course Attendance
**GET** `/attendance/course/{courseId}`

Get attendance data for all students in a course (teachers/admins only).

**Parameters:**
- `courseId` (integer, required) - Course ID
- `date` (string, optional) - Filter by specific date (YYYY-MM-DD)

**Response:**
```json
{
  "success": true,
  "data": {
    "course": {
      "id": 3,
      "name": "Introduction to Programming",
      "code": "CS101"
    },
    "attendance_by_date": [
      {
        "date": "2025-12-07",
        "total_students": 25,
        "present_count": 23,
        "absent_count": 1,
        "late_count": 1,
        "excused_count": 0,
        "records": [
          {
            "id": 4,
            "status": "present",
            "notes": "Participated actively in class discussion",
            "student": {
              "id": 1,
              "name": "John Doe",
              "student_id": "STU001"
            }
          }
        ]
      }
    ]
  }
}
```

---

## Status Codes

- **200** - Success
- **201** - Created
- **400** - Bad Request (validation errors)
- **401** - Unauthorized (authentication required)
- **403** - Forbidden (insufficient permissions)
- **404** - Not Found
- **422** - Unprocessable Entity (validation failed)
- **500** - Internal Server Error

---

## Error Response Format

```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field_name": [
      "Validation error message"
    ]
  }
}
```

---

## Validation Rules

### Assignment Creation
- `course_id`: required, must exist in courses table
- `title`: required, string, max 255 characters
- `description`: required, string
- `type`: required, in: project, homework, quiz, exam, lab
- `total_points`: required, numeric, min: 1
- `due_date`: required, date format
- `instructions`: optional, string

### Assignment Grade Submission
- `student_id`: required, must exist and be enrolled in the course
- `assignment_id`: required, must exist
- `score`: required, numeric, min: 0, max: assignment total_points
- `feedback`: optional, string, max 1000 characters

### Attendance Marking
- `student_id`: required, must exist and be enrolled in the course
- `course_id`: required, must exist
- `date`: required, date format
- `status`: required, in: present, absent, late, excused
- `notes`: optional, string, max 500 characters

---

## Rate Limiting

API endpoints are rate-limited to prevent abuse:
- **60 requests per minute** for authenticated users
- **10 requests per minute** for unauthenticated requests

---

## Changelog

### Week 3 Features (December 2025)
- Added Assignment Management endpoints
- Implemented Assignment Grading system
- Created comprehensive GPA calculation
- Built Attendance tracking system
- Enhanced error handling and validation
- Added bulk operations for attendance marking
- Implemented real-time statistics and analytics
