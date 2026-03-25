<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Seeder;

class NormalizeCustomerAssignmentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $salesUserIds = User::query()
            ->where('role', 'sales')
            ->pluck('id')
            ->all();

        if (count($salesUserIds) === 0) {
            return;
        }

        $fallbackSalesId = (int) $salesUserIds[0];

        Customer::query()
            ->whereNotNull('assigned_user_id')
            ->whereHas('assignedUser', function ($query): void {
                $query->where('role', '!=', 'sales');
            })
            ->chunkById(100, function ($customers) use ($fallbackSalesId): void {
                foreach ($customers as $customer) {
                    $customer->update([
                        'assigned_user_id' => $fallbackSalesId,
                        'assignment_status' => 'pending',
                        'assignment_reviewed_by' => null,
                        'assignment_reviewed_at' => null,
                    ]);
                }
            });
    }
}
