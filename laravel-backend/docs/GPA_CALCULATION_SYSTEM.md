# GPA Calculation System - Documentation

## Overview
The GPA (Grade Point Average) calculation system provides comprehensive grade tracking, calculation, and reporting functionality for the Academic Portal.

## Features Implemented

### 1. **Grade Point Mapping**
- Standard letter grade scale (A+ to F)
- **5.0 Grade point scale** (0.00 to 5.00)
- Percentage ranges for each grade
- Pass/fail indicators
- Grade descriptions

### 2. **GPA Calculation Types**
- **Semester GPA**: Calculate GPA for a specific semester
- **Cumulative GPA (CGPA)**: Calculate overall GPA across all semesters  
- **Course Grade**: Calculate final grade for individual courses
- **Batch GPA**: Calculate GPA for multiple students (rankings)

### 3. **Academic Standing**
Automatic determination based on CGPA:
- **5.00 - 4.75**: Dean's List / Summa Cum Laude
- **4.74 - 4.50**: Magna Cum Laude
- **4.49 - 4.00**: Cum Laude
- **3.99 - 3.50**: Good Standing
- **3.49 - 3.00**: Satisfactory Standing
- **2.99 - 2.50**: Academic Warning
- **Below 2.50**: Academic Probation

## Database Schema

### Grade Points Table
```sql
- id (primary key)
- letter_grade (string, unique) - e.g., "A+", "B-"
- min_percentage (decimal) - Minimum percentage for grade
- max_percentage (decimal) - Maximum percentage for grade
- grade_point (decimal) - GPA value (0.00-5.00)
- description (string) - e.g., "Excellent", "Good"
- is_passing (boolean) - Whether grade passes
- order (integer) - Display order
- timestamps
```

### Standard Grading Scale (5.0 Scale)
| Letter | Range | Points | Description | Pass |
|--------|-------|--------|-------------|------|
| A+ | 97-100 | 5.00 | Exceptional | ✓ |
| A | 93-96.99 | 4.75 | Excellent | ✓ |
| A- | 90-92.99 | 4.50 | Very Good | ✓ |
| B+ | 87-89.99 | 4.00 | Good | ✓ |
| B | 83-86.99 | 3.50 | Above Average | ✓ |
| B- | 80-82.99 | 3.00 | Average | ✓ |
| C+ | 77-79.99 | 2.50 | Fair | ✓ |
| C | 73-76.99 | 2.00 | Satisfactory | ✓ |
| C- | 70-72.99 | 1.50 | Minimum Pass | ✓ |
| D+ | 67-69.99 | 1.25 | Below Average | ✓ |
| D | 60-66.99 | 1.00 | Poor | ✓ |
| F | 0-59.99 | 0.00 | Fail | ✗ |

## API Endpoints

### 1. Get Student Cumulative GPA
```http
GET /api/students/{studentId}/gpa
Authorization: Bearer {token}
```

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
    "gpa_details": {
      "cgpa": 3.45,
      "total_credits": 45,
      "total_quality_points": 155.25,
      "academic_standing": "Cum Laude",
      "semesters": [
        {
          "semester": 1,
          "gpa": 3.50,
          "total_credits": 15,
          "courses": [...]
        }
      ]
    }
  }
}
```

### 2. Get Semester GPA
```http
GET /api/students/{studentId}/gpa/semester/{semester}
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "semester_gpa": {
      "semester": 1,
      "gpa": 3.50,
      "total_credits": 15,
      "quality_points": 52.50,
      "courses": [
        {
          "course_id": 1,
          "course_code": "CS101",
          "course_name": "Introduction to Programming",
          "credits": 3,
          "percentage": 92.50,
          "letter_grade": "A-",
          "grade_point": 3.67,
          "quality_points": 11.01
        }
      ]
    }
  }
}
```

### 3. Get Course Grade
```http
GET /api/students/{studentId}/courses/{courseId}/grade
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "percentage": 85.50,
    "letter_grade": "B",
    "grade_point": 3.00,
    "description": "Above Average",
    "is_passing": true,
    "assignments": [
      {
        "assignment_title": "Assignment 1",
        "marks_obtained": 85,
        "total_marks": 100,
        "percentage": 85.00,
        "weight": 100
      }
    ]
  }
}
```

### 4. Get Course Statistics
```http
GET /api/courses/{courseId}/gpa-statistics
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "total_students": 30,
    "average_grade": 78.50,
    "highest_grade": 98.00,
    "lowest_grade": 55.00,
    "pass_rate": 86.67,
    "grade_distribution": {
      "A": 5,
      "B": 10,
      "C": 8,
      "D": 3,
      "F": 4
    }
  }
}
```

### 5. Get Class Rankings
```http
GET /api/students/rankings
Authorization: Bearer {token}
```

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "rank": 1,
      "student_id": 5,
      "student_name": "Jane Smith",
      "student_code": "STU002",
      "cgpa": 3.92,
      "total_credits": 48,
      "academic_standing": "Dean's List / Summa Cum Laude"
    },
    {
      "rank": 2,
      "student_id": 1,
      "student_name": "John Doe",
      "student_code": "STU001",
      "cgpa": 3.85,
      "total_credits": 45,
      "academic_standing": "Magna Cum Laude"
    }
  ]
}
```

### 6. Get Batch GPA
```http
POST /api/students/gpa/batch
Authorization: Bearer {token}
Content-Type: application/json

{
  "student_ids": [1, 2, 3, 5]
}
```

### 7. Get All Grade Points
```http
GET /api/grade-points
Authorization: Bearer {token}
```

### 8. Calculate Letter Grade
```http
POST /api/grade-points/calculate
Authorization: Bearer {token}
Content-Type: application/json

{
  "percentage": 87.5
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "percentage": 87.5,
    "grade_info": {
      "letter_grade": "B+",
      "grade_point": 3.33,
      "description": "Good",
      "is_passing": true
    }
  }
}
```

### 9. Update Student CGPA
```http
PUT /api/students/{studentId}/gpa/update
Authorization: Bearer {token}
```

## GPA Calculation Logic

### Formula
```
GPA = Total Quality Points / Total Credits

Quality Points = Grade Point × Course Credits
```

### Example Calculation
```
Course 1: 3 credits × 5.00 (A+) = 15.00 quality points
Course 2: 3 credits × 4.00 (B+) = 12.00 quality points
Course 3: 4 credits × 4.50 (A-) = 18.00 quality points

Total: 10 credits, 45.00 quality points
GPA = 45.00 / 10 = 4.50
```

## Service Methods

### GpaCalculationService

#### `calculateSemesterGpa(int $studentId, int $semester)`
Calculates GPA for a specific semester including all enrolled courses.

#### `calculateCumulativeGpa(int $studentId)`
Calculates overall CGPA across all semesters with breakdown by semester.

#### `calculateCourseGrade(int $studentId, int $courseId)`
Calculates final grade for a specific course based on all assignments.

#### `getLetterGrade(float $percentage)`
Converts percentage to letter grade and grade point.

#### `getAcademicStanding(float $cgpa)`
Determines academic standing based on CGPA.

#### `calculateBatchGpa(array $studentIds)`
Calculates GPA for multiple students and ranks them.

#### `getCourseGpaStatistics(int $courseId)`
Provides statistical analysis of course performance.

#### `updateStudentCgpa(int $studentId)`
Updates student record with latest calculated CGPA.

## Usage Examples

### Frontend Integration
```javascript
// Get student GPA
const response = await fetch('/api/students/1/gpa', {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json'
  }
});
const data = await response.json();
console.log(`CGPA: ${data.data.gpa_details.cgpa}`);

// Calculate letter grade
const gradeResponse = await fetch('/api/grade-points/calculate', {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({ percentage: 85.5 })
});
```

### Backend Usage
```php
use App\Services\GpaCalculationService;

$gpaService = new GpaCalculationService();

// Calculate student CGPA
$cgpa = $gpaService->calculateCumulativeGpa($studentId);

// Get course grade
$grade = $gpaService->calculateCourseGrade($studentId, $courseId);

// Get class rankings
$rankings = $gpaService->calculateBatchGpa([1, 2, 3, 4, 5]);
```

## Permissions Required

- `grades.read` - View GPA and grades
- `grades.update` - Update GPA calculations
- `grades.create` - Create grade records
- `grades.delete` - Delete grade records

## Notes

1. **Automatic Updates**: Student CGPA is updated automatically when grades change
2. **Weighted Calculations**: Courses are weighted by credit hours
3. **Real-time Calculation**: GPA calculated on-demand from current grades
4. **Historical Tracking**: Semester-by-semester breakdown maintained
5. **Performance**: Optimized queries with eager loading

## Migration Commands

```bash
# Run migrations
php artisan migrate

# Seed grade points
php artisan db:seed --class=GradePointSeeder

# Refresh with seeding
php artisan migrate:fresh --seed
```

## Testing

```bash
# Test GPA calculation
php artisan tinker
>>> $service = new App\Services\GpaCalculationService();
>>> $cgpa = $service->calculateCumulativeGpa(1);
>>> dd($cgpa);
```

## Future Enhancements

1. **Grade Appeals** - Process for grade change requests
2. **GPA Trends** - Historical GPA tracking and visualization
3. **Predictive GPA** - Calculate required grades for target GPA
4. **Transfer Credits** - Handle credits from other institutions
5. **Honors Calculation** - Automatic honors designation
6. **PDF Transcripts** - Generate official transcripts
7. **Email Notifications** - Alert students of GPA changes
8. **Grade Curves** - Support for curved grading
9. **Weighted GPA** - Support for honors/AP courses
10. **Grade Import** - Bulk import from Excel/CSV
