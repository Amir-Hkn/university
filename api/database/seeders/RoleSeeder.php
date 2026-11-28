<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'student' => 'Student',
            'teacher' => 'Teacher',
            'education' => 'Education',
            'store_management' => 'Store Management',
            'site_admin' => 'Site Admin',
            'support' => 'Support',
        ];

        $timestamp = now();

        $records = [];

        foreach ($roles as $code => $name) {
            $records[] = [
                'code' => $code,
                'name' => $name,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        DB::table('roles')->upsert(
            $records,
            ['code'],
            ['name', 'updated_at']
        );
    }
}
