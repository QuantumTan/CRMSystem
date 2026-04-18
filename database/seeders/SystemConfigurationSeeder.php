<?php

namespace Database\Seeders;

use App\Models\SystemConfiguration;
use Illuminate\Database\Seeder;

class SystemConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (SystemConfiguration::query()->exists()) {
            return;
        }

        SystemConfiguration::query()->create(SystemConfiguration::defaults());
    }
}
