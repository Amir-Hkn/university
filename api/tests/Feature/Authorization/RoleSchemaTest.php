
<?php

use Database\Seeders\RoleSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

test('roles table has the required columns', function () {
    expect(Schema::hasTable('roles'))->toBeTrue();

    expect(Schema::hasColumns('roles', [
        'id',
        'code',
        'name',
        'created_at',
        'updated_at',
    ]))->toBeTrue();
});

test('role seeder creates all six predefined roles', function () {
    $this->seed(RoleSeeder::class);

    $codes = DB::table('roles')
        ->orderBy('code')
        ->pluck('code')
        ->all();

    expect($codes)->toBe([
        'education',
        'site_admin',
        'store_management',
        'student',
        'support',
        'teacher',
    ]);
});

test('role seeder does not create duplicate roles', function () {
    $this->seed(RoleSeeder::class);

    $originalIds = DB::table('roles')
        ->pluck('id', 'code')
        ->all();

    $this->seed(RoleSeeder::class);

    $currentIds = DB::table('roles')
        ->pluck('id', 'code')
        ->all();

    expect(DB::table('roles')->count())->toBe(6);

    expect($currentIds)->toEqual($originalIds);
});

test('duplicate role codes are rejected', function () {
    $this->seed(RoleSeeder::class);

    expect(fn () => DB::table('roles')->insert([
        'code' => 'student',
        'name' => 'Duplicate Student',
    ]))->toThrow(QueryException::class, 'SQLSTATE[23505]');
});

test('unsupported role codes are rejected', function () {
    expect(fn () => DB::table('roles')->insert([
        'code' => 'super_admin',
        'name' => 'Super Admin',
    ]))->toThrow(QueryException::class, 'SQLSTATE[23514]');
});
