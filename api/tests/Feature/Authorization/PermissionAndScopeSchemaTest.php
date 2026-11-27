<?php

use App\Models\AcademicProgram;
use App\Models\Permission;
use App\Models\Role;
use App\Models\StaffPermissionGrant;
use App\Models\StaffProgramScope;
use App\Models\User;
use Database\Seeders\AcademicProgramSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->seed(AcademicProgramSeeder::class);

    $role = Role::query()
        ->where('code', 'education')
        ->firstOrFail();

    $this->staffA = User::factory()
        ->forRole($role)
        ->create();

    $this->staffB = User::factory()
        ->forRole($role)
        ->create();

    $this->programA = AcademicProgram::query()
        ->where('code', 'DEMO-CE-BSC')
        ->firstOrFail();

    $this->programB = AcademicProgram::query()
        ->where('code', 'DEMO-IT-BSC')
        ->firstOrFail();

    $this->permissionId = DB::table('permissions')
        ->insertGetId([
            'code' => 'identity.verification.review',
            'description' => 'Review identity verification',
        ]);

    $this->scopeA = DB::table('staff_program_scopes')
        ->insertGetId([
            'user_id' => $this->staffA->id,
            'program_id' => $this->programA->id,
        ]);

    $this->secondScopeA = DB::table('staff_program_scopes')
        ->insertGetId([
            'user_id' => $this->staffA->id,
            'program_id' => $this->programB->id,
        ]);

    $this->scopeB = DB::table('staff_program_scopes')
        ->insertGetId([
            'user_id' => $this->staffB->id,
            'program_id' => $this->programA->id,
        ]);

    $this->grantData = [
        'user_id' => $this->staffA->id,
        'permission_id' => $this->permissionId,
        'staff_program_scope_id' => $this->scopeA,
    ];
});

test('authorization tables have required columns', function () {
    expect(Schema::hasColumns('permissions', [
        'id',
        'code',
        'description',
        'created_at',
        'updated_at',
    ]))->toBeTrue();

    expect(Schema::hasColumns('staff_program_scopes', [
        'id',
        'user_id',
        'program_id',
        'created_at',
        'updated_at',
    ]))->toBeTrue();

    expect(Schema::hasColumns('staff_permission_grants', [
        'id',
        'user_id',
        'permission_id',
        'staff_program_scope_id',
        'created_at',
        'updated_at',
    ]))->toBeTrue();
});

test('duplicate permission codes are rejected', function () {
    expect(fn () => DB::table('permissions')->insert([
        'code' => 'identity.verification.review',
    ]))->toThrow(QueryException::class, 'SQLSTATE[23505]');
});

test('blank or untrimmed permission codes are rejected', function (string $code) {
    expect(fn () => DB::table('permissions')->insert([
        'code' => $code,
    ]))->toThrow(QueryException::class, 'SQLSTATE[23514]');
})->with([
    '',
    '   ',
    ' identity.test',
    'identity.test ',
]);

test('permission code cannot be null', function () {
    expect(fn () => DB::table('permissions')->insert([
        'code' => null,
    ]))->toThrow(QueryException::class, 'SQLSTATE[23502]');
});

test('a staff member can have multiple program scopes', function () {
    expect(DB::table('staff_program_scopes')
        ->where('user_id', $this->staffA->id)
        ->count())->toBe(2);
});

test('multiple staff members can share the same program', function () {
    expect(DB::table('staff_program_scopes')
        ->where('program_id', $this->programA->id)
        ->count())->toBe(2);
});

test('duplicate staff program scopes are rejected', function () {
    expect(fn () => DB::table('staff_program_scopes')->insert([
        'user_id' => $this->staffA->id,
        'program_id' => $this->programA->id,
    ]))->toThrow(QueryException::class, 'SQLSTATE[23505]');
});

test('program scopes must reference existing records', function (string $column) {
    $scope = [
        'user_id' => $this->staffA->id,
        'program_id' => $this->programA->id,
    ];

    $scope[$column] = -1;

    expect(fn () => DB::table('staff_program_scopes')->insert($scope))
        ->toThrow(QueryException::class, 'SQLSTATE[23503]');
})->with([
    'user_id',
    'program_id',
]);

test('program scope foreign keys cannot be null', function (string $column) {
    $scope = [
        'user_id' => $this->staffA->id,
        'program_id' => $this->programA->id,
    ];

    $scope[$column] = null;

    expect(fn () => DB::table('staff_program_scopes')->insert($scope))
        ->toThrow(QueryException::class, 'SQLSTATE[23502]');
})->with([
    'user_id',
    'program_id',
]);

test('a permission can be granted within an owned scope', function () {
    DB::table('staff_permission_grants')
        ->insert($this->grantData);

    $this->assertDatabaseHas(
        'staff_permission_grants',
        $this->grantData
    );
});

test('a permission cannot use another staff members scope', function () {
    $grant = $this->grantData;

    $grant['staff_program_scope_id'] = $this->scopeB;

    expect(fn () => DB::table('staff_permission_grants')->insert($grant))
        ->toThrow(QueryException::class, 'SQLSTATE[23503]');
});

test('permission grants must reference existing records', function (string $column) {
    $grant = $this->grantData;

    $grant[$column] = -1;

    expect(fn () => DB::table('staff_permission_grants')->insert($grant))
        ->toThrow(QueryException::class, 'SQLSTATE[23503]');
})->with([
    'user_id',
    'permission_id',
    'staff_program_scope_id',
]);

test('required grant foreign keys cannot be null', function (string $column) {
    $grant = $this->grantData;

    $grant[$column] = null;

    expect(fn () => DB::table('staff_permission_grants')->insert($grant))
        ->toThrow(QueryException::class, 'SQLSTATE[23502]');
})->with([
    'user_id',
    'permission_id',
]);

test('duplicate scoped permission grants are rejected', function () {
    DB::table('staff_permission_grants')
        ->insert($this->grantData);

    expect(fn () => DB::table('staff_permission_grants')
        ->insert($this->grantData))
        ->toThrow(QueryException::class, 'SQLSTATE[23505]');
});

test('the same permission can be assigned to different owned scopes', function () {
    DB::table('staff_permission_grants')
        ->insert($this->grantData);

    DB::table('staff_permission_grants')->insert([
        ...$this->grantData,
        'staff_program_scope_id' => $this->secondScopeA,
    ]);

    expect(DB::table('staff_permission_grants')
        ->where('user_id', $this->staffA->id)
        ->where('permission_id', $this->permissionId)
        ->count())->toBe(2);
});

test('the schema accepts grants without a program scope', function () {
    $grant = [
        ...$this->grantData,
        'staff_program_scope_id' => null,
    ];

    DB::table('staff_permission_grants')->insert($grant);

    $this->assertDatabaseHas(
        'staff_permission_grants',
        $grant
    );
});

test('duplicate grants with null scope are rejected', function () {
    $grant = [
        ...$this->grantData,
        'staff_program_scope_id' => null,
    ];

    DB::table('staff_permission_grants')->insert($grant);

    expect(fn () => DB::table('staff_permission_grants')->insert($grant))
        ->toThrow(QueryException::class, 'SQLSTATE[23505]');
});

test('grants without scope still require a valid user', function () {
    $grant = [
        ...$this->grantData,
        'user_id' => -1,
        'staff_program_scope_id' => null,
    ];

    expect(fn () => DB::table('staff_permission_grants')->insert($grant))
        ->toThrow(QueryException::class, 'SQLSTATE[23503]');
});

test('referenced authorization records cannot be deleted', function (string $record) {
    DB::table('staff_permission_grants')
        ->insert($this->grantData);

    [$table, $id] = match ($record) {
        'scope' => [
            'staff_program_scopes',
            $this->scopeA,
        ],
        'permission' => [
            'permissions',
            $this->permissionId,
        ],
        'user' => [
            'users',
            $this->staffA->id,
        ],
        'program' => [
            'academic_programs',
            $this->programA->id,
        ],
    };

    expect(fn () => DB::table($table)
        ->where('id', $id)
        ->delete())
        ->toThrow(QueryException::class, 'SQLSTATE[23503]');
})->with([
    'scope',
    'permission',
    'user',
    'program',
]);

test('authorization model relationships work correctly', function () {
    $grant = new StaffPermissionGrant();

    $grant->user_id = $this->staffA->id;
    $grant->permission_id = $this->permissionId;
    $grant->staff_program_scope_id = $this->scopeA;

    $grant->save();

    $scope = StaffProgramScope::query()
        ->findOrFail($this->scopeA);

    $permission = Permission::query()
        ->findOrFail($this->permissionId);

    expect($grant->user->is($this->staffA))->toBeTrue();

    expect($grant->permission->is($permission))->toBeTrue();

    expect($grant->programScope->is($scope))->toBeTrue();

    expect($scope->user->is($this->staffA))->toBeTrue();

    expect($scope->program->is($this->programA))->toBeTrue();

    expect($scope->permissionGrants()->count())->toBe(1);

    expect($permission->grants()->count())->toBe(1);
});

test('a grant with null scope has no related program scope', function () {
    $id = DB::table('staff_permission_grants')
        ->insertGetId([
            ...$this->grantData,
            'staff_program_scope_id' => null,
        ]);

    $grant = StaffPermissionGrant::query()->findOrFail($id);

    expect($grant->programScope)->toBeNull();
});

test('authorization attributes are protected from mass assignment', function () {
    $permission = new Permission();
    $scope = new StaffProgramScope();
    $grant = new StaffPermissionGrant();

    expect($permission->isFillable('code'))->toBeFalse();
    expect($permission->isFillable('description'))->toBeFalse();

    expect($scope->isFillable('user_id'))->toBeFalse();
    expect($scope->isFillable('program_id'))->toBeFalse();

    expect($grant->isFillable('user_id'))->toBeFalse();
    expect($grant->isFillable('permission_id'))->toBeFalse();
    expect($grant->isFillable('staff_program_scope_id'))->toBeFalse();
});
