<?php

use App\Models\AcademicProgram;
use Database\Seeders\AcademicProgramSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

test('academic programs table has the required columns', function () {
    expect(Schema::hasTable('academic_programs'))->toBeTrue();

    expect(Schema::hasColumns('academic_programs', [
        'id',
        'code',
        'name',
        'degree_level',
        'created_at',
        'updated_at',
    ]))->toBeTrue();
});

test('academic program model can create a program', function () {
    $program = AcademicProgram::query()->create([
        'code' => 'TEST-CS-BSC',
        'name' => 'Computer Science',
        'degree_level' => 'bachelor',
    ]);

    $this->assertDatabaseHas('academic_programs', [
        'id' => $program->id,
        'code' => 'TEST-CS-BSC',
        'name' => 'Computer Science',
        'degree_level' => 'bachelor',
    ]);
});

test('academic program seeder creates all reference programs', function () {
    $this->seed(AcademicProgramSeeder::class);

    $codes = AcademicProgram::query()
        ->orderBy('code')
        ->pluck('code')
        ->all();

    expect($codes)->toBe([
        'DEMO-BA-BSC',
        'DEMO-CE-BSC',
        'DEMO-IT-BSC',
    ]);
});

test('academic program seeder does not create duplicates or change IDs', function () {
    $this->seed(AcademicProgramSeeder::class);

    $originalIds = AcademicProgram::query()
        ->pluck('id', 'code')
        ->all();

    $this->seed(AcademicProgramSeeder::class);

    $currentIds = AcademicProgram::query()
        ->pluck('id', 'code')
        ->all();

    expect(AcademicProgram::query()->count())->toBe(3);

    expect($currentIds)->toEqual($originalIds);
});

test('duplicate academic program codes are rejected', function () {
    $this->seed(AcademicProgramSeeder::class);

    expect(fn () => DB::table('academic_programs')->insert([
        'code' => 'DEMO-CE-BSC',
        'name' => 'Duplicate Program',
        'degree_level' => 'bachelor',
    ]))->toThrow(QueryException::class, 'SQLSTATE[23505]');
});

test('required academic program attributes cannot be null', function (string $attribute) {
    $program = [
        'code' => 'TEST-CS-BSC',
        'name' => 'Computer Science',
        'degree_level' => 'bachelor',
    ];

    $program[$attribute] = null;

    expect(fn () => DB::table('academic_programs')->insert($program))
        ->toThrow(QueryException::class, 'SQLSTATE[23502]');
})->with([
    'code',
    'name',
    'degree_level',
]);

test('required academic program attributes cannot be blank', function (string $attribute, string $value) {
    $program = [
        'code' => 'TEST-CS-BSC',
        'name' => 'Computer Science',
        'degree_level' => 'bachelor',
    ];

    $program[$attribute] = $value;

    expect(fn () => DB::table('academic_programs')->insert($program))
        ->toThrow(QueryException::class, 'SQLSTATE[23514]');
})->with([
    ['code', ''],
    ['code', '   '],
    ['name', ''],
    ['name', '   '],
    ['degree_level', ''],
    ['degree_level', '   '],
    ['code', "\t"],
    ['code', "\n"],
    ['name', "\t"],
    ['name', "\n"],
    ['degree_level', "\t"],
    ['degree_level', "\n"],
]);
