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
        Schema::table('enrollment_confirmations', function (Blueprint $table) {
            $table->boolean('admin_override')->default(false);
            $table->foreignId('override_by')->nullable()->constrained('users');
            $table->text('override_reason')->nullable();
            
            $table->index('admin_override');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollment_confirmations', function (Blueprint $table) {
            $table->dropForeign(['override_by']);
            $table->dropColumn([
                'admin_override',
                'override_by',
                'override_reason'
            ]);
        });
    }
};
