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
        Schema::table('registrations', function (Blueprint $table) {
            $table->foreignId('fees_verified_by')->nullable()->constrained('users');
            $table->timestamp('fees_verified_at')->nullable();
            $table->boolean('registration_blocked')->default(false);
            $table->foreignId('blocked_by')->nullable()->constrained('users');
            $table->timestamp('blocked_at')->nullable();
            $table->text('block_reason')->nullable();
            $table->foreignId('override_by')->nullable()->constrained('users');
            $table->timestamp('override_at')->nullable();
            $table->text('override_reason')->nullable();
            
            $table->index('registration_blocked');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropForeign(['fees_verified_by']);
            $table->dropForeign(['blocked_by']);
            $table->dropForeign(['override_by']);
            $table->dropColumn([
                'fees_verified_by',
                'fees_verified_at',
                'registration_blocked',
                'blocked_by',
                'blocked_at',
                'block_reason',
                'override_by',
                'override_at',
                'override_reason'
            ]);
        });
    }
};
