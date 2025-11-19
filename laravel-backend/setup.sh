#!/bin/bash

# Academic Nexus Portal - Laravel Backend Setup Script
echo "🎓 Academic Nexus Portal - Laravel Backend Setup"
echo "================================================"

# Check if we're in the right directory
if [ ! -f "composer.json" ]; then
    echo "❌ Error: Please run this script from the laravel-backend directory"
    exit 1
fi

# Step 1: Create necessary directories and set permissions
echo "📁 Creating necessary directories..."
mkdir -p storage/app/public
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/testing
mkdir -p bootstrap/cache

# Set proper permissions
chmod -R 755 storage
chmod -R 755 bootstrap/cache

# Step 2: Copy environment file and generate key
echo "🔧 Setting up environment..."
if [ ! -f ".env" ]; then
    cp .env.example .env
    echo "✅ .env file created from .env.example"
else
    echo "ℹ️  .env file already exists"
fi

# Step 3: Generate application key
echo "🔐 Generating application key..."
php artisan key:generate

# Step 4: Run package discovery
echo "📦 Discovering packages..."
php artisan package:discover

# Step 5: Create storage link
echo "🔗 Creating storage link..."
php artisan storage:link

# Step 6: Clear and cache config
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo ""
echo "✅ Laravel backend setup completed!"
echo ""
echo "🔄 Next Steps:"
echo "1. Update your .env file with database credentials:"
echo "   DB_DATABASE=academic_nexus_portal"
echo "   DB_USERNAME=root"
echo "   DB_PASSWORD=root"
echo ""
echo "2. Create the database in MySQL:"
echo "   CREATE DATABASE academic_nexus_portal;"
echo ""
echo "3. Run migrations and seed data:"
echo "   php artisan migrate"
echo "   php artisan db:seed"
echo ""
echo "4. Start the development server:"
echo "   php artisan serve --host=0.0.0.0 --port=8000"
echo ""
echo "🎉 Your API will be available at: http://localhost:8000/api"
