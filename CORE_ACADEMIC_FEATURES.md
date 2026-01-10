# Core Academic Features - Implementation Plan

**Status:** In Progress  
**Date:** January 9, 2026  
**Priority:** HIGH

---

## Overview

This document outlines the implementation of critical core academic features that were missing from the initial system. These features are essential for a fully functional academic management portal.

---

## Database Schema

### 1. Timetables/Course Scheduling

**Table:** `timetables`

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| course_id | bigint | Foreign key to courses |
| teacher_id | bigint | Foreign key to teachers |
| room_number | string | Classroom number |
| building | string | Building name/code |
| day_of_week | enum | Monday-Sunday |
| start_time | time | Class start time |
| end_time | time | Class end time |
| semester | integer | Semester number |
| academic_year | year | Academic year |
| section | string | Section identifier (A, B, C, etc.) |
| capacity | integer | Maximum students (default: 50) |
| enrolled_count | integer | Current enrollment count |
| status | enum | active, cancelled, completed |
| notes | text | Additional notes |

**Features:**
- Automatic conflict detection (same teacher, same time)
- Room double-booking prevention
- Capacity management
- Multiple sections support

---

### 2. Academic Calendar

**Table:** `academic_calendars`

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| title | string | Event name |
| event_type | enum | semester_start, semester_end, exam_period, registration_period, holiday, break, orientation, graduation, other |
| start_date | date | Event start date |
| end_date | date | Event end date |
| semester | integer | Related semester (nullable) |
| academic_year | year | Academic year |
| description | text | Detailed description |
| is_holiday | boolean | Holiday flag |
| status | enum | scheduled, ongoing, completed, cancelled |

**Features:**
- Semester management
- Exam period scheduling
- Registration windows
- Holiday tracking
- Event notifications

---

### 3. Course Prerequisites

**Table:** `course_prerequisites`

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| course_id | bigint | Course with prerequisite |
| prerequisite_course_id | bigint | Required prerequisite course |
| minimum_grade | decimal(3,2) | Minimum grade required (nullable) |
| requirement_type | enum | required, recommended, corequisite |

**Requirement Types:**
- **required:** Must complete before enrollment
- **recommended:** Suggested but not mandatory
- **corequisite:** Must take concurrently or have completed

**Features:**
- Automatic prerequisite checking during enrollment
- Multiple prerequisites per course
- Grade requirement validation
- Corequisite support

---

### 4. Course Waitlists

**Table:** `course_waitlists`

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| course_id | bigint | Foreign key to courses |
| student_id | bigint | Foreign key to students |
| position | integer | Position in queue |
| semester | integer | Semester |
| academic_year | year | Academic year |
| status | enum | waiting, enrolled, removed, expired |
| added_at | timestamp | When added to waitlist |
| enrolled_at | timestamp | When enrolled (nullable) |
| removed_at | timestamp | When removed (nullable) |
| notes | text | Additional notes |

**Features:**
- Automatic position management
- Auto-enrollment when space available
- Notification system for waitlist movement
- Expiration handling

---

### 5. Degree Programs

**Table:** `degree_programs`

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| department_id | bigint | Foreign key to departments |
| name | string | Program name (e.g., "Bachelor of Science in Computer Science") |
| code | string | Program code (e.g., "BSCS") - unique |
| degree_type | enum | associate, bachelor, master, doctorate, certificate, diploma |
| total_credits_required | integer | Total credits to graduate |
| duration_years | integer | Program duration in years |
| duration_semesters | integer | Program duration in semesters |
| minimum_cgpa | decimal(3,2) | Minimum CGPA to graduate (default: 2.00) |
| description | text | Program description |
| program_objectives | json | Learning outcomes/objectives |
| status | enum | active, inactive, archived |

**Features:**
- Multiple degree types support
- Credit requirement tracking
- CGPA graduation requirements
- Program objectives management

---

### 6. Program Requirements

**Table:** `program_requirements`

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| degree_program_id | bigint | Foreign key to degree_programs |
| course_id | bigint | Foreign key to courses |
| requirement_type | enum | core, major, minor, elective, general_education, capstone |
| semester_recommended | integer | Recommended semester (nullable) |
| is_required | boolean | Required flag (default: true) |
| credits | integer | Course credits |
| sort_order | integer | Display order |

**Requirement Types:**
- **core:** Required core courses
- **major:** Major-specific courses
- **minor:** Minor requirements
- **elective:** Elective courses
- **general_education:** Gen ed requirements
- **capstone:** Final project/thesis

---

### 7. Enhanced Student Table

**Added Fields to `students` table:**

| Column | Type | Description |
|--------|------|-------------|
| degree_program_id | bigint | Foreign key to degree_programs (nullable) |
| total_credits_earned | integer | Total credits completed (default: 0) |
| current_semester | integer | Current semester number (default: 1) |
| expected_graduation_date | date | Projected graduation date (nullable) |
| academic_status | enum | active, probation, suspended, graduated, withdrawn, on_leave |

**Academic Status Values:**
- **active:** Currently enrolled and in good standing
- **probation:** Academic probation (low CGPA)
- **suspended:** Temporarily suspended
- **graduated:** Completed program
- **withdrawn:** Voluntarily withdrawn
- **on_leave:** On leave of absence

---

### 8. Enhanced Courses Table

**Added Fields to `courses` table:**

| Column | Type | Description |
|--------|------|-------------|
| grade_components | json | Weighted grading components (e.g., {"assignments": 30, "midterm": 30, "final": 40}) |
| has_curve | boolean | Curve grading enabled (default: false) |
| curve_percentage | decimal(5,2) | Curve percentage (nullable) |
| max_capacity | integer | Maximum enrollment (default: 50) |
| min_capacity | integer | Minimum enrollment (default: 10) |

---

## API Endpoints (Planned)

### Timetable Management

```
GET    /api/timetables                          # List all schedules
POST   /api/admin/timetables                    # Create schedule
GET    /api/timetables/{id}                     # Get schedule details
PUT    /api/admin/timetables/{id}               # Update schedule
DELETE /api/admin/timetables/{id}               # Delete schedule
GET    /api/timetables/student/{studentId}     # Student's schedule
GET    /api/timetables/teacher/{teacherId}     # Teacher's schedule
GET    /api/timetables/room/{room}             # Room schedule
GET    /api/timetables/conflicts               # Check for conflicts
```

### Academic Calendar

```
GET    /api/academic-calendar                   # List all events
POST   /api/admin/academic-calendar             # Create event
GET    /api/academic-calendar/{id}              # Get event details
PUT    /api/admin/academic-calendar/{id}        # Update event
DELETE /api/admin/academic-calendar/{id}        # Delete event
GET    /api/academic-calendar/semester/{sem}    # Events by semester
GET    /api/academic-calendar/upcoming          # Upcoming events
```

### Course Prerequisites

```
GET    /api/courses/{courseId}/prerequisites    # Get course prerequisites
POST   /api/admin/prerequisites                 # Add prerequisite
DELETE /api/admin/prerequisites/{id}            # Remove prerequisite
POST   /api/courses/{courseId}/check-eligibility/{studentId}  # Check if student eligible
```

### Waitlist Management

```
GET    /api/courses/{courseId}/waitlist         # Get course waitlist
POST   /api/courses/{courseId}/waitlist         # Join waitlist
DELETE /api/waitlist/{id}                       # Leave waitlist
GET    /api/student/waitlists                   # Student's waitlists
POST   /api/admin/waitlist/{id}/enroll          # Manually enroll from waitlist
```

### Degree Programs

```
GET    /api/degree-programs                     # List all programs
POST   /api/admin/degree-programs               # Create program
GET    /api/degree-programs/{id}                # Get program details
PUT    /api/admin/degree-programs/{id}          # Update program
DELETE /api/admin/degree-programs/{id}          # Delete program
GET    /api/degree-programs/{id}/requirements   # Get program requirements
```

### Degree Progress Tracking

```
GET    /api/students/{studentId}/progress       # Get degree progress
GET    /api/students/{studentId}/transcript     # Generate transcript
GET    /api/students/{studentId}/graduation-check  # Check graduation eligibility
POST   /api/admin/students/{studentId}/calculate-progress  # Recalculate progress
```

---

## Business Logic

### 1. Course Enrollment with Prerequisites

**Flow:**
1. Student attempts to enroll in course
2. System checks prerequisites:
   - Has student completed all required prerequisites?
   - Did student meet minimum grade requirements?
   - Are corequisites being taken concurrently or already completed?
3. If prerequisites not met: Return error with missing prerequisites
4. If prerequisites met: Check course capacity
5. If course full: Offer waitlist option
6. If space available: Complete enrollment

### 2. Weighted Grade Calculation

**Example:**
```json
{
  "assignments": 30,
  "quizzes": 10,
  "midterm": 25,
  "final": 35
}
```

**Calculation:**
```
Final Grade = (assignments_score * 0.30) + (quizzes_score * 0.10) + 
              (midterm_score * 0.25) + (final_score * 0.35)
```

**With Curve:**
```
Curved Grade = Final Grade + (curve_percentage)
Max = 100
```

### 3. Degree Progress Calculation

**Progress Percentage:**
```
Progress = (total_credits_earned / total_credits_required) * 100
```

**Completion by Requirement Type:**
- Core courses: X/Y completed
- Major courses: X/Y completed
- Electives: X credits earned / Y credits required
- General Education: X/Y completed

### 4. Waitlist Auto-Enrollment

**Trigger:** When a student drops a course or capacity increases

**Process:**
1. Find waitlist entries for course with status 'waiting'
2. Order by position (ascending)
3. For each student in waitlist order:
   - Check if student still eligible (prerequisites, etc.)
   - If eligible: Enroll and update waitlist status
   - If not eligible: Skip and check next
   - Update positions for remaining students

### 5. Timetable Conflict Detection

**Check For:**
- Same teacher, same time slot
- Same room, same time slot
- Student enrolled in two classes at same time

**Validation:**
```php
// Pseudo-code
function hasConflict($teacherId, $day, $startTime, $endTime) {
    return Timetable::where('teacher_id', $teacherId)
        ->where('day_of_week', $day)
        ->where(function($q) use ($startTime, $endTime) {
            $q->whereBetween('start_time', [$startTime, $endTime])
              ->orWhereBetween('end_time', [$startTime, $endTime]);
        })->exists();
}
```

---

## Implementation Priority

### Phase 1: Foundation (Immediate)
1. Run migrations
2. Create and configure models
3. Set up relationships

### Phase 2: Core Controllers (Week 1)
1. TimetableController - Schedule management
2. AcademicCalendarController - Calendar events
3. PrerequisiteController - Prerequisite checking

### Phase 3: Enrollment Logic (Week 2)
1. Enhanced EnrollmentController with prerequisite checking
2. WaitlistController - Waitlist management
3. Auto-enrollment logic

### Phase 4: Degree Tracking (Week 3)
1. DegreeProgramController
2. ProgressTrackingController
3. TranscriptController

### Phase 5: Advanced Features (Week 4)
1. Weighted grade calculations
2. Curve grading implementation
3. Conflict detection algorithms
4. Automated notifications

---

## Testing Requirements

### Unit Tests
- Prerequisite checking logic
- Weighted grade calculations
- Conflict detection
- Waitlist position management

### Integration Tests
- Enrollment flow with prerequisites
- Waitlist auto-enrollment
- Degree progress calculation
- Transcript generation

### Edge Cases
- Circular prerequisites
- Concurrent enrollment attempts (race conditions)
- Multiple waitlist entries
- Invalid schedule conflicts

---

## Migration Command

```bash
# Run all new migrations
php artisan migrate

# If you need to rollback
php artisan migrate:rollback --step=8

# Fresh migration (WARNING: Drops all tables)
php artisan migrate:fresh --seed
```

---

## Next Steps

1. Review and approve database schema
2. Run migrations
3. Create model relationships
4. Implement controllers and services
5. Add API routes
6. Create seeders with test data
7. Write tests
8. Update API documentation
9. Create frontend components

---

## Notes

- All prerequisite checking should happen before enrollment
- Waitlist should auto-process when spots open
- Degree progress should recalculate on grade submission
- Timetable conflicts must be prevented, not just warned
- Academic calendar events should trigger notifications
- Consider performance: index all frequently queried fields
- Implement caching for degree progress calculations

---

**Status:** Database schema complete, ready for controller implementation
