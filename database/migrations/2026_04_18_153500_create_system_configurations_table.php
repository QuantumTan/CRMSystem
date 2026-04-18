<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('app_name')->default('NexLink CRM');
            $table->string('company_email')->nullable();
            $table->string('company_phone', 50)->nullable();
            $table->text('company_address')->nullable();
            $table->string('default_lead_status', 50)->default('new');
            $table->string('default_lead_priority', 50)->default('medium');
            $table->string('currency_code', 10)->default('PHP');
            $table->unsignedSmallInteger('password_reset_expire_minutes')->default(60);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_configurations');
    }
};
