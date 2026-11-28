
<?php

use App\Enums\UserStatus;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RoleSeeder::class);
});

test('users table has the required identity columns', function () {
    expect(Schema::hasColumns('users', [
        'id',
        'role_id',
        'name',
        'mobile',
        'email',
        'password',
        'status',
        'mobile_verified_at',
        'email_verified_at',
        'remember_token',
        'created_at',
        'updated_at',
    ]))->toBeTrue();
});

test('a user belongs to one role', function () {
    $user = User::factory()->create();

    expect($user->role)
        ->toBeInstanceOf(Role::class);

    expect($user->role->code)
        ->toBe('student');
});

test('a role can have multiple users', function () {
    $role = Role::query()
        ->where('code', 'student')
        ->firstOrFail();

    User::factory()
        ->count(3)
        ->forRole($role)
        ->create();

    expect($role->users()->count())->toBe(3);
});

test('users cannot be created without a role', function () {
    expect(fn () => User::factory()->create([
        'role_id' => null,
    ]))->toThrow(QueryException::class, 'SQLSTATE[23502]');
});

test('users cannot reference an invalid role', function () {
    expect(fn () => User::factory()->create([
        'role_id' => -1,
    ]))->toThrow(QueryException::class, 'SQLSTATE[23503]');
});

test('assigned roles cannot be deleted', function () {
    $user = User::factory()->create();

    expect(fn () => $user->role->delete())
        ->toThrow(QueryException::class, 'SQLSTATE[23503]');
});

test('duplicate mobile numbers are rejected', function () {
    $user = User::factory()->create();

    expect(fn () => User::factory()->create([
        'mobile' => $user->mobile,
    ]))->toThrow(QueryException::class, 'SQLSTATE[23505]');
});

test('duplicate email addresses are rejected', function () {
    $user = User::factory()->create();

    expect(fn () => User::factory()->create([
        'email' => $user->email,
    ]))->toThrow(QueryException::class, 'SQLSTATE[23505]');
});

test('users can have mobile only credentials', function () {
    $user = User::factory()->create([
        'email' => null,
        'email_verified_at' => null,
        'password' => null,
    ]);

    expect($user->mobile)->not->toBeNull();
    expect($user->email)->toBeNull();
    expect($user->password)->toBeNull();
});

test('users can have email and password credentials', function () {
    $user = User::factory()->create([
        'mobile' => null,
        'mobile_verified_at' => null,
    ]);

    expect($user->email)->not->toBeNull();
    expect($user->password)->not->toBeNull();
});

test('users must have valid login credentials', function () {
    expect(fn () => User::factory()->create([
        'mobile' => null,
        'mobile_verified_at' => null,
        'email' => null,
        'email_verified_at' => null,
        'password' => null,
    ]))->toThrow(QueryException::class, 'SQLSTATE[23514]');
});

test('email only accounts must have a password', function () {
    expect(fn () => User::factory()->create([
        'mobile' => null,
        'mobile_verified_at' => null,
        'password' => null,
    ]))->toThrow(QueryException::class, 'SQLSTATE[23514]');
});

test('invalid mobile formats are rejected', function () {
    expect(fn () => User::factory()->create([
        'mobile' => '09123456789',
    ]))->toThrow(QueryException::class, 'SQLSTATE[23514]');
});

test('non normalized emails are rejected', function () {
    expect(fn () => User::factory()->create([
        'email' => 'Student@EXAMPLE.COM',
    ]))->toThrow(QueryException::class, 'SQLSTATE[23514]');
});

test('invalid account statuses are rejected', function () {
    $user = User::factory()->create();

    expect(fn () => DB::table('users')
        ->where('id', $user->id)
        ->update(['status' => 'unknown']))
        ->toThrow(QueryException::class, 'SQLSTATE[23514]');
});

test('account status is cast to an enum', function () {
    $user = User::factory()->create();

    expect($user->status)->toBe(UserStatus::Active);
});

test('suspended user factory state works', function () {
    $user = User::factory()->suspended()->create();

    expect($user->status)->toBe(UserStatus::Suspended);
});

test('mobile verification requires a mobile number', function () {
    expect(fn () => User::factory()->create([
        'mobile' => null,
    ]))->toThrow(QueryException::class, 'SQLSTATE[23514]');
});

test('email verification requires an email address', function () {
    expect(fn () => User::factory()->create([
        'email' => null,
    ]))->toThrow(QueryException::class, 'SQLSTATE[23514]');
});

test('sensitive account attributes are protected from mass assignment', function () {
    $user = new User();

    foreach ([
                 'role_id',
                 'status',
                 'mobile_verified_at',
                 'email_verified_at',
             ] as $attribute) {
        expect($user->isFillable($attribute))->toBeFalse();
    }
});

test('user factory can create unverified contact details', function () {
    $user = User::factory()
        ->unverified()
        ->mobileUnverified()
        ->create();

    expect($user->email_verified_at)->toBeNull();
    expect($user->mobile_verified_at)->toBeNull();
});

test('passwords are hashed and hidden', function () {
    $user = User::factory()->create([
        'password' => 'test-password',
    ]);

    expect(\Illuminate\Support\Facades\Hash::check(
        'test-password',
        $user->password
    ))->toBeTrue();

    expect($user->toArray())
        ->not->toHaveKey('password')
        ->not->toHaveKey('remember_token');
});
