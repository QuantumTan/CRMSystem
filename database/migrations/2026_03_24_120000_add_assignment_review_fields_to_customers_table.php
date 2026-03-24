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
        Schema::table('customers', function (Blueprint $table) {
            $table->enum('assignment_status', ['pending', 'approved', 'rejected'])
                ->default('pending')
                ->after('assigned_user_id');
            $table->foreignId('assignment_reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->after('assignment_status');
            $table->timestamp('assignment_reviewed_at')
                ->nullable()
                ->after('assignment_reviewed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assignment_reviewed_by');
            $table->dropColumn(['assignment_status', 'assignment_reviewed_at']);
        });
    }
};
