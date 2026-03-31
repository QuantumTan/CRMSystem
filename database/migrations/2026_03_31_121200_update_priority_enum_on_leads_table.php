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
        Schema::table('leads', function (Blueprint $table) {
            // Change the priority column to the correct enum values
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            // Revert to string if needed (adjust as appropriate for your previous type)
            $table->string('priority')->default('medium')->change();
        });
    }
};
