# 🔐 Login Credentials Guide

## ⚠️ IMPORTANT: Backend Must Be Running!

Before you can log in, you **MUST** start the Laravel backend server.

---

## 🚀 Step 1: Start the Laravel Backend

Open a **new terminal** and run:

```bash
cd /Applications/MAMP/htdocs/academic-nexus-portal/laravel-backend
php artisan serve
```

This will start the server at: `http://localhost:8000`

**Keep this terminal running!** Do not close it.

---

## 🔑 Step 2: Use These Test Credentials

### Admin Login
- **Email**: `admin@academic-nexus.com`
- **Password**: `admin123`
- **Role**: Select "Admin" from dropdown

### Teacher Login
- **Email**: `john.smith@academic-nexus.com` OR `sarah.johnson@academic-nexus.com`
- **Password**: `teacher123`
- **Role**: Select "Teacher" from dropdown

### Student Login
- **Email**: Check DatabaseSeeder.php for student emails
- **Password**: `student123`
- **Role**: Select "Student" from dropdown

---

## 🐛 Troubleshooting

### Problem: "Failed to load resource: 401 (Unauthorized)"

**Solution**: This means your backend is not running or credentials are wrong.

1. **Check if backend is running**:
   ```bash
   # In a new terminal
   curl http://localhost:8000/api/health
   ```
   
   If you get a response, backend is running ✅
   If you get "Connection refused", backend is NOT running ❌

2. **Start the backend**:
   ```bash
   cd /Applications/MAMP/htdocs/academic-nexus-portal/laravel-backend
   php artisan serve
   ```

3. **Check database is set up**:
   ```bash
   cd /Applications/MAMP/htdocs/academic-nexus-portal/laravel-backend
   php artisan migrate:fresh --seed
   ```

### Problem: "CORS Error"

**Solution**: Make sure Laravel CORS is configured:

1. Check `laravel-backend/config/cors.php`:
   ```php
   'paths' => ['api/*', 'sanctum/csrf-cookie'],
   'allowed_origins' => ['http://localhost:5173'],
   'supports_credentials' => true,
   ```

2. Restart backend after changes

### Problem: "The provided credentials do not match our records"

**Solutions**:
1. Make sure you selected the correct **role** (admin/teacher/student)
2. Check you're using the exact email and password
3. Reseed the database:
   ```bash
   php artisan migrate:fresh --seed
   ```

---

## 📋 Quick Start Checklist

- [ ] Laravel backend running (`php artisan serve`)
- [ ] Database migrated and seeded (`php artisan migrate:fresh --seed`)
- [ ] React frontend running (`npm run dev`)
- [ ] Using correct credentials from above
- [ ] Selected correct role in dropdown
- [ ] Backend accessible at `http://localhost:8000`

---

## 🎯 Complete Setup Instructions

### First Time Setup

1. **Start Backend**:
   ```bash
   # Terminal 1
   cd laravel-backend
   composer install
   cp .env.example .env
   php artisan key:generate
   php artisan migrate:fresh --seed
   php artisan serve
   ```

2. **Start Frontend** (in a new terminal):
   ```bash
   # Terminal 2
   npm install
   npm run dev
   ```

3. **Open Browser**:
   ```
   http://localhost:5173
   ```

4. **Login**:
   - Select role: Admin
   - Email: admin@academic-nexus.com
   - Password: admin123
   - Click "Login"

---

## 🔍 How to Check Backend Status

Run this in terminal:
```bash
curl http://localhost:8000/api/health
```

Expected response:
```json
{
  "status": "ok",
  "timestamp": "2026-01-18..."
}
```

---

## 💡 Common Issues

### 1. Port 8000 Already in Use
```bash
# Kill existing process
lsof -ti:8000 | xargs kill -9
# Then start again
php artisan serve
```

### 2. Database Not Seeded
```bash
cd laravel-backend
php artisan migrate:fresh --seed
```

### 3. Frontend Can't Connect
- Check `.env` file has: `VITE_API_URL=http://localhost:8000/api`
- Restart frontend: `npm run dev`

---

## 📞 Need Help?

If login still fails:

1. Check browser console (F12) for errors
2. Check Laravel logs: `laravel-backend/storage/logs/laravel.log`
3. Verify both servers are running:
   - Backend: `http://localhost:8000` ✅
   - Frontend: `http://localhost:5173` ✅

---

**Status**: Ready for testing! 🚀

Make sure both servers are running before attempting to log in.
