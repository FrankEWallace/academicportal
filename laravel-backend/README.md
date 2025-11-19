# Academic Nexus Portal - Laravel Backend API

A comprehensive Laravel backend API for the Academic Nexus Portal, providing complete academic management system functionality.

## 🚀 Features

- **Multi-role Authentication** (Admin, Student, Teacher)
- **Student Management System**
- **Course & Department Management**
- **Attendance Tracking**
- **Grade Management**
- **Fee Management**
- **Announcements System**
- **RESTful API Architecture**
- **Role-based Access Control**
- **CORS Support for React Frontend**

## 📋 Prerequisites

- PHP 8.2 or higher
- Composer
- MySQL 8.0 or higher
- Laravel 11.x

## 🛠️ Installation & Setup

### 1. Install Dependencies
```bash
cd /Applications/MAMP/htdocs/academic-nexus-portal/laravel-backend
composer install
```

### 2. Environment Setup
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 3. Database Configuration
Edit your `.env` file with your database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=academic_nexus_portal
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 4. Create Database
```sql
CREATE DATABASE academic_nexus_portal;
```

### 5. Run Migrations & Seeders
```bash
# Run database migrations
php artisan migrate

# Seed initial data
php artisan db:seed
```

### 6. Install Laravel Sanctum
```bash
php artisan vendor:publish --provider="Laravel\\Sanctum\\SanctumServiceProvider"
```

### 7. Start Development Server
```bash
php artisan serve --host=0.0.0.0 --port=8000
```

## 🔐 Default Login Credentials

After running seeders, you can use these credentials:

### Admin
- **Email:** admin@academic-nexus.com
- **Password:** admin123
- **Role:** admin

### Teacher
- **Email:** john.smith@academic-nexus.com
- **Password:** teacher123
- **Role:** teacher

### Student
- **Email:** john.doe@student.academic-nexus.com
- **Password:** student123
- **Role:** student

## 📡 API Endpoints

### Authentication
```
POST   /api/auth/login          # Login user
POST   /api/auth/logout         # Logout user
GET    /api/auth/me             # Get current user
POST   /api/auth/refresh        # Refresh token
```

### Admin Routes
```
GET    /api/admin/dashboard     # Admin dashboard stats
GET    /api/admin/students      # Get all students
POST   /api/admin/students      # Create new student
GET    /api/admin/teachers      # Get all teachers
POST   /api/admin/teachers      # Create new teacher
GET    /api/admin/courses       # Get all courses
POST   /api/admin/courses       # Create new course
GET    /api/admin/departments   # Get all departments
```

### Student Routes
```
GET    /api/student/dashboard   # Student dashboard
GET    /api/student/courses     # Student courses
GET    /api/student/grades      # Student grades
GET    /api/student/attendance  # Student attendance
GET    /api/student/fees        # Student fees
```

### Teacher Routes
```
GET    /api/teacher/dashboard   # Teacher dashboard
GET    /api/teacher/courses     # Teacher courses
POST   /api/teacher/attendance  # Mark attendance
GET    /api/teacher/attendance  # Get attendance records
POST   /api/teacher/grades      # Submit grades
```

## 🗄️ Database Schema

### Core Tables
- `users` - User authentication and basic info
- `students` - Student-specific information
- `teachers` - Teacher-specific information
- `departments` - Academic departments
- `courses` - Course catalog
- `enrollments` - Student course registrations
- `attendances` - Attendance records
- `grades` - Grade records
- `fees` - Fee management
- `announcements` - System announcements

## 🔧 Configuration

### CORS Configuration
The API is configured to accept requests from your React frontend running on `http://localhost:8080`. Update the CORS middleware if your frontend runs on a different port.

### Sanctum Configuration
Laravel Sanctum is configured for API token authentication. The tokens are used for stateless API authentication between your React frontend and Laravel backend.

## 🚀 Deployment

### Production Setup
1. Set `APP_ENV=production` in `.env`
2. Set `APP_DEBUG=false` in `.env`
3. Configure your production database
4. Run `composer install --optimize-autoloader --no-dev`
5. Run `php artisan config:cache`
6. Run `php artisan route:cache`
7. Run `php artisan view:cache`

### Web Server Configuration
Point your web server document root to the `public` directory.

## 🔍 API Testing

You can test the API endpoints using tools like:
- Postman
- Insomnia
- Thunder Client (VS Code extension)

### Example API Request
```bash
# Login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@academic-nexus.com",
    "password": "admin123",
    "role": "admin"
  }'

# Use the returned token for authenticated requests
curl -X GET http://localhost:8000/api/admin/dashboard \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

## 📁 Project Structure
```
laravel-backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/
│   │   │   ├── AuthController.php
│   │   │   ├── AdminController.php
│   │   │   ├── StudentController.php
│   │   │   └── TeacherController.php
│   │   └── Middleware/
│   │       ├── Cors.php
│   │       └── RoleMiddleware.php
│   └── Models/
│       ├── User.php
│       ├── Student.php
│       ├── Teacher.php
│       ├── Course.php
│       ├── Department.php
│       ├── Enrollment.php
│       ├── Attendance.php
│       ├── Grade.php
│       ├── Fee.php
│       └── Announcement.php
├── database/
│   ├── migrations/
│   └── seeders/
├── routes/
│   └── api.php
└── public/
    └── index.php
```

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License.

## 🆘 Support

For support, email support@academic-nexus.com or create an issue in the repository.

---

**Happy Coding! 🎓**
