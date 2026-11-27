<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AcademicProgramSeeder extends Seeder
{
    public function run(): void
    {
        $programs = [
            [
                'code' => 'DEMO-CE-BSC',
                'name' => 'Computer Engineering',
                'degree_level' => 'bachelor',
            ],
            [
                'code' => 'DEMO-IT-BSC',
                'name' => 'Information Technology',
                'degree_level' => 'bachelor',
            ],
            [
                'code' => 'DEMO-BA-BSC',
                'name' => 'Business Administration',
                'degree_level' => 'bachelor',
            ],
        ];

        $timestamp = now();

        $records = [];

        foreach ($programs as $program) {
            $records[] = [
                ...$program,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        }

        DB::table('academic_programs')->upsert(
            $records,
            ['code'],
            ['name', 'degree_level', 'updated_at']
        );
    }
}
