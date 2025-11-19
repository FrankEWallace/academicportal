# Academic Nexus Portal - Laravel Backend Setup

## Project Structure
Your Laravel backend should have the following structure for the Academic Portal:

## Database Schema

### Core Tables
1. **users** - Main user authentication table
2. **roles** - User roles (admin, student, teacher, parent)
3. **students** - Student-specific information
4. **teachers** - Teacher-specific information
5. **courses** - Course catalog
6. **departments** - Academic departments
7. **enrollments** - Student course registrations
8. **attendance** - Attendance records
9. **grades** - Grade records
10. **announcements** - System announcements
11. **fees** - Fee structure and payments

## API Endpoints Structure

### Authentication Endpoints
- POST /api/auth/login
- POST /api/auth/logout
- POST /api/auth/refresh
- GET /api/auth/me

### Admin Endpoints
- GET /api/admin/dashboard
- GET /api/admin/students
- POST /api/admin/students
- GET /api/admin/teachers
- POST /api/admin/teachers
- GET /api/admin/courses
- POST /api/admin/courses

### Student Endpoints
- GET /api/student/dashboard
- GET /api/student/courses
- GET /api/student/grades
- GET /api/student/attendance

### Teacher Endpoints
- GET /api/teacher/dashboard
- GET /api/teacher/courses
- POST /api/teacher/attendance
- POST /api/teacher/grades

## Next Steps

1. Configure your Laravel backend database
2. Set up authentication with Laravel Sanctum
3. Create models and migrations
4. Set up API controllers
5. Configure CORS for your React frontend
