<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class userSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $timestamp = now();

        DB::table('users')->upsert([
            [
                'name' => 'Admin User',
                'email' => 'admin@comp.com',
                'email_verified_at' => $timestamp,
                'password' => '$2y$10$RH8b5BldXLNXkGggrziSpe/AVXfUheIXgGJZAzkoSCR5YwatYkP6y',
                'role' => 'admin',
                'remember_token' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
                'deleted_at' => null,
            ],
            [
                'name' => 'Manager User',
                'email' => 'manager@comp.com',
                'email_verified_at' => $timestamp,
                'password' => '$2y$10$6C8M9aBDlVBtgoNtm4WDVeiKOkAQKPxpb87NpW8l.9jueFan71zN2',
                'role' => 'manager',
                'remember_token' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
                'deleted_at' => null,
            ],
            [
                'name' => 'Sales User 1',
                'email' => 'sales@comp.com',
                'email_verified_at' => $timestamp,
                'password' => '$2y$10$.yDK8RPitxWhbpxiKO3Mq.96xVKfIb4HwBASxk3OTkRXaB2OZrVve',
                'role' => 'sales',
                'remember_token' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
                'deleted_at' => null,
            ],
            [
                'name' => 'Sales User 2',
                'email' => 'sales2@comp.com',
                'email_verified_at' => $timestamp,
                'password' => '$2y$10$6/VizjnxPj9Nd6xamGRA1.01R/fOrsKVtgqSqzuNh/tmEIJvMNQli',
                'role' => 'sales',
                'remember_token' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
                'deleted_at' => null,
            ],
            [
                'name' => 'Sales User 3',
                'email' => 'sales3@comp.com',
                'email_verified_at' => $timestamp,
                'password' => '$2y$10$YGfdTY77JlFYJNBPgl6pO.uOFhdqQW9ihkjp2emrGFSaYxemqhc.q',
                'role' => 'sales',
                'remember_token' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
                'deleted_at' => null,
            ],
            [
                'name' => 'Sales User 4',
                'email' => 'sales4@comp.com',
                'email_verified_at' => $timestamp,
                'password' => '$2y$10$Jtfz16Zzey1lVL7..jRIwOxckflsHTL//CW71gqcfO.MtT8v1Q5qC',
                'role' => 'sales',
                'remember_token' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
                'deleted_at' => null,
            ],
            [
                'name' => 'Sales User 5',
                'email' => 'sales5@comp.com',
                'email_verified_at' => $timestamp,
                'password' => '$2y$10$gRyJ4VRZnpzFDQKT4jMMpupnEwzBfbNEdAGlQPvwJX9cdhWkRPvjW',
                'role' => 'sales',
                'remember_token' => null,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
                'deleted_at' => null,
            ],
        ], ['email'], [
            'name',
            'email_verified_at',
            'password',
            'role',
            'remember_token',
            'updated_at',
            'deleted_at',
        ]);
    }
}
