# 🎓 Academic Nexus Portal

A comprehensive academic management system built with React (Frontend) and Laravel (Backend). This portal provides complete functionality for managing students, teachers, courses, attendance, grades, and fees in an educational institution.

## 🌟 Features

### **Multi-Role System**
- **Admin Dashboard** - Complete institutional management
- **Student Portal** - Course enrollment, grades, attendance tracking
- **Teacher Portal** - Class management, attendance marking, grade submission

### **Core Functionality**
- 🔐 **Authentication & Authorization** - Role-based access control
- 👥 **User Management** - Students, teachers, and administrators  
- 📚 **Course Management** - Course catalog, enrollment, scheduling
- 📊 **Attendance System** - Real-time attendance tracking
- 📈 **Grade Management** - Assessment and grading system
- 💰 **Fee Management** - Payment tracking and financial records
- 📢 **Announcements** - Institution-wide communication

## 🛠️ Technology Stack

### **Frontend (React)**
- **Framework**: React 18 with TypeScript
- **Build Tool**: Vite
- **Styling**: Tailwind CSS + shadcn/ui components
- **State Management**: React Query (TanStack Query)
- **Routing**: React Router v6

### **Backend (Laravel)**
- **Framework**: Laravel 11.x
- **Database**: MySQL 8.0+
- **Authentication**: Laravel Sanctum (API tokens)
- **API**: RESTful API architecture
- **ORM**: Eloquent

## 🚀 Quick Start

### **Prerequisites**
- Node.js 18+
- PHP 8.2+
- Composer
- MySQL 8.0+
- MAMP/XAMPP (for local development)

### **Installation**

1. **Clone the repository**
   ```bash
   git clone https://github.com/FrankEWallace/academicportal.git
   cd academicportal
   ```

2. **Frontend Setup**
   ```bash
   # Install dependencies
   npm install
   
   # Start development server
   npm run dev
   # Frontend will run on http://localhost:8080
   ```

3. **Backend Setup**
   ```bash
   cd laravel-backend
   
   # Install PHP dependencies
   composer install
   
   # Setup environment
   cp .env.example .env
   php artisan key:generate
   
   # Configure database in .env file
   DB_DATABASE=academic_nexus_portal
   DB_USERNAME=root
   DB_PASSWORD=your_password
   
   # Run migrations and seed data
   php artisan migrate
   php artisan db:seed
   
   # Start Laravel server
   php artisan serve --host=0.0.0.0 --port=8000
   # Backend API will run on http://localhost:8000/api
   ```

## 📱 Demo Credentials

After running database seeders:

| Role | Email | Password |
|------|-------|----------|
| **Admin** | admin@academic-nexus.com | admin123 |
| **Teacher** | john.smith@academic-nexus.com | teacher123 |
| **Student** | john.doe@student.academic-nexus.com | student123 |

## 📡 API Endpoints

### **Authentication**
```
POST   /api/auth/login     # User login
POST   /api/auth/logout    # User logout
GET    /api/auth/me        # Get current user
POST   /api/auth/refresh   # Refresh token
```

### **Admin Routes**
```
GET    /api/admin/dashboard     # Admin statistics
GET    /api/admin/students      # Manage students
GET    /api/admin/teachers      # Manage teachers
GET    /api/admin/courses       # Manage courses
GET    /api/admin/departments   # Manage departments
```

### **Student Routes**
```
GET    /api/student/dashboard   # Student dashboard
GET    /api/student/courses     # Enrolled courses
GET    /api/student/grades      # Academic grades
GET    /api/student/attendance  # Attendance records
GET    /api/student/fees        # Fee information
```

### **Teacher Routes**
```
GET    /api/teacher/dashboard   # Teacher dashboard
GET    /api/teacher/courses     # Assigned courses
POST   /api/teacher/attendance  # Mark attendance
POST   /api/teacher/grades      # Submit grades
```

## 🗄️ Database Schema

### **Core Tables**
- `users` - Authentication and basic user information
- `students` - Student profiles and academic data
- `teachers` - Faculty information and qualifications
- `departments` - Academic department organization
- `courses` - Course catalog and scheduling
- `enrollments` - Student course registrations
- `attendances` - Daily attendance records
- `grades` - Assessment and grading data
- `fees` - Financial records and payments
- `announcements` - System-wide communications

## 🏗️ Project Structure

```
academic-nexus-portal/
├── 📁 src/                          # React Frontend
│   ├── 📁 components/               # Reusable UI components
│   ├── 📁 pages/                    # Application pages/views
│   ├── 📁 hooks/                    # Custom React hooks
│   └── 📁 lib/                      # Utility functions
├── 📁 laravel-backend/              # Laravel Backend
│   ├── 📁 app/Models/               # Eloquent models
│   ├── 📁 app/Http/Controllers/Api/ # API controllers
│   ├── 📁 database/migrations/      # Database migrations
│   └── 📁 routes/                   # API routes
└── 📄 README.md                     # Project documentation
```

## 🔧 Development

### **Frontend Development**
```bash
npm run dev          # Start development server
npm run build        # Build for production
npm run preview      # Preview production build
npm run lint         # Run ESLint
```

### **Backend Development**
```bash
php artisan serve              # Start development server
php artisan migrate:fresh      # Reset database
php artisan db:seed            # Seed sample data
php artisan route:list         # List all routes
```

## 🚀 Deployment

### **Frontend (Vite Build)**
```bash
npm run build
# Deploy dist/ folder to your web server
```

### **Backend (Laravel)**
```bash
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- Built with [Lovable](https://lovable.dev) for rapid React development
- UI components from [shadcn/ui](https://ui.shadcn.com/)
- Icons from [Lucide React](https://lucide.dev/)
- Backend powered by [Laravel](https://laravel.com/)

---

**🎓 Academic Nexus Portal - Transforming Education Management**
- Edit files directly within the Codespace and commit and push your changes once you're done.

## What technologies are used for this project?

This project is built with:

- Vite
- TypeScript
- React
- shadcn-ui
- Tailwind CSS

## How can I deploy this project?

Simply open [Lovable](https://lovable.dev/projects/48282f7f-8a73-4409-9453-7a4178e400b7) and click on Share -> Publish.

## Can I connect a custom domain to my Lovable project?

Yes, you can!

To connect a domain, navigate to Project > Settings > Domains and click Connect Domain.

Read more here: [Setting up a custom domain](https://docs.lovable.dev/features/custom-domain#custom-domain)
