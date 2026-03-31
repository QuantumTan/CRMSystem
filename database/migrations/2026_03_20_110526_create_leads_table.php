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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('source')->nullable();

            $table->enum('status', [
                'new',
                'contacted',
                'qualified',
                'proposal_sent',
                'negotiation',
                'won',
                'lost'
            ])->default('new');

            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->decimal('expected_value', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            // conversion lead tracking 
            $table->foreignId('converted_to_customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->timestamp('converted_at')->nullable();
            // lost lead
            $table->string('lost_reason')->nullable();
            $table->string('lost_category')->nullable();
            $table->timestamp('lost_at')->nullable();
            // lead id
            $table->string('lead_id')->unique()->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('priority');
            $table->index('assigned_user_id');
            $table->index('converted_to_customer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
