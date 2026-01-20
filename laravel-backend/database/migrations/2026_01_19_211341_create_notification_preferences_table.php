<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('email_enabled')->default(true);
            $table->boolean('sms_enabled')->default(false);
            $table->boolean('push_enabled')->default(true);
            
            // Email notification preferences
            $table->boolean('email_grades')->default(true);
            $table->boolean('email_payments')->default(true);
            $table->boolean('email_announcements')->default(true);
            $table->boolean('email_attendance')->default(true);
            $table->boolean('email_timetable')->default(true);
            
            // SMS notification preferences
            $table->boolean('sms_grades')->default(false);
            $table->boolean('sms_payments')->default(false);
            $table->boolean('sms_urgent')->default(true);
            
            // In-app notification preferences
            $table->boolean('app_all')->default(true);
            
            $table->timestamps();
            
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_preferences');
    }
};
