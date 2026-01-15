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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hostel_id')->constrained('hostels')->onDelete('cascade');
            $table->string('room_number', 50);
            $table->unsignedInteger('floor');
            $table->unsignedInteger('capacity');
            $table->unsignedInteger('current_occupancy')->default(0);
            $table->enum('status', ['available', 'occupied', 'full', 'maintenance'])->default('available');
            $table->text('amenities')->nullable(); // JSON field for room-specific amenities
            $table->timestamps();
            
            $table->unique(['hostel_id', 'room_number'], 'unique_room_per_hostel');
            $table->index('hostel_id');
            $table->index('status');
            $table->index('floor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
