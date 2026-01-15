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
        Schema::create('accommodation_amenities', function (Blueprint $table) {
            $table->id();
            $table->string('hostel_name');
            $table->string('amenity_name'); // e.g., "Wi-Fi", "Study Room", "Laundry", "Common Kitchen"
            $table->string('icon')->nullable(); // icon name for frontend
            $table->boolean('is_available')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index('hostel_name');
            $table->index('is_available');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accommodation_amenities');
    }
};
