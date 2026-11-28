<?php

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

uses(DatabaseMigrations::class);

test('identity schema supports rollback and migration on an isolated database', function () {
    $tables = [
        'roles',
        'users',
        'academic_programs',
        'permissions',
        'staff_program_scopes',
        'staff_permission_grants',
        'otp_challenges',
        'identity_verification_cases',
        'verification_documents',
        'provider_match_results',
        'verification_reviews',
    ];

    // DatabaseMigrations has created a fresh schema.

    foreach ($tables as $table) {
        expect(Schema::hasTable($table))->toBeTrue();
    }

    // Roll back the latest hardening migration.

    $this->artisan('migrate:rollback', [
        '--step' => 1,
    ])->assertExitCode(0);

    expect(Schema::hasIndex(
        'identity_verification_cases',
        'identity_verification_cases_user_id_index'
    ))->toBeTrue();

    // Create a legacy consumed OTP containing a verifier.

    $issuedAt = now()->subMinutes(5);

    $otpId = DB::table('otp_challenges')->insertGetId([
        'user_id' => null,
        'destination' => '+12025550199',
        'purpose' => 'registration',
        'context_id' => (string) Str::uuid(),
        'secret_verifier' => str_repeat('a', 64),
        'issued_at' => $issuedAt,
        'expires_at' => $issuedAt->copy()->addMinutes(2),
        'consumed_at' => $issuedAt->copy()->addMinute(),
    ]);

    // Reapply hardening and verify legacy verifier removal.

    $this->artisan('migrate')->assertExitCode(0);

    $otp = DB::table('otp_challenges')
        ->where('id', $otpId)
        ->first();

    expect($otp)->not->toBeNull();
    expect($otp->secret_verifier)->toBeNull();
    expect($otp->consumed_at)->not->toBeNull();

    expect(Schema::hasIndex(
        'identity_verification_cases',
        'ivc_user_created_at_index'
    ))->toBeTrue();

    // Remove only the synthetic fixture so the previous
    // schema can be restored safely.

    DB::table('otp_challenges')
        ->where('id', $otpId)
        ->delete();

    // Roll back every migration.

    $this->artisan('migrate:reset')
        ->assertExitCode(0);

    foreach ($tables as $table) {
        expect(Schema::hasTable($table))->toBeFalse();
    }

    // Rebuild the entire schema.

    $this->artisan('migrate')
        ->assertExitCode(0);

    foreach ($tables as $table) {
        expect(Schema::hasTable($table))->toBeTrue();
    }

    expect(Schema::hasIndex(
        'identity_verification_cases',
        'ivc_user_created_at_index'
    ))->toBeTrue();
});
