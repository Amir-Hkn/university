<?php

use App\Enums\IdentityVerificationStatus;
use App\Enums\ProviderMatchOutcome;
use App\Enums\VerificationReviewDecision;
use App\Models\AcademicProgram;
use App\Models\IdentityVerificationCase;
use App\Models\OtpChallenge;
use App\Models\Role;
use App\Models\StaffPermissionGrant;
use App\Models\User;
use Database\Seeders\AcademicProgramSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

test('identity and authorization records work together', function () {
    $this->seed(RoleSeeder::class);
    $this->seed(AcademicProgramSeeder::class);

    // Accounts and academic reference.

    $studentRole = Role::query()
        ->where('code', 'student')
        ->firstOrFail();

    $educationRole = Role::query()
        ->where('code', 'education')
        ->firstOrFail();

    $student = User::factory()
        ->forRole($studentRole)
        ->create();

    $reviewer = User::factory()
        ->forRole($educationRole)
        ->create();

    $program = AcademicProgram::query()
        ->where('code', 'DEMO-CE-BSC')
        ->firstOrFail();

    // Staff authorization records.

    $permissionId = DB::table('permissions')->insertGetId([
        'code' => 'identity.verification.review',
        'description' => 'Review student identity verification',
    ]);

    $scopeId = DB::table('staff_program_scopes')->insertGetId([
        'user_id' => $reviewer->id,
        'program_id' => $program->id,
    ]);

    $grantId = DB::table('staff_permission_grants')->insertGetId([
        'user_id' => $reviewer->id,
        'permission_id' => $permissionId,
        'staff_program_scope_id' => $scopeId,
    ]);

    // Consumed registration OTP.

    $otpId = DB::table('otp_challenges')->insertGetId([
        'user_id' => $student->id,
        'destination' => $student->mobile,
        'purpose' => 'registration',
        'context_id' => (string) Str::uuid(),
        'secret_verifier' => null,
        'attempts' => 1,
        'issued_at' => now()->subMinutes(10),
        'expires_at' => now()->subMinutes(8),
        'consumed_at' => now()->subMinutes(9),
    ]);

    // Submitted identity verification case.

    $caseId = DB::table('identity_verification_cases')->insertGetId([
        'user_id' => $student->id,
        'requested_program_id' => $program->id,
        'national_id_ciphertext' => Crypt::encryptString('0012345678'),
        'birth_date' => '2000-01-01',
        'submitted_mobile' => $student->mobile,
        'status' => 'submitted',
        'submitted_at' => now()->subMinutes(5),
    ]);

    // Required verification documents.

    foreach ([
                 'national_id_image',
                 'latest_diploma_image',
             ] as $kind) {
        DB::table('verification_documents')->insert([
            'case_id' => $caseId,
            'kind' => $kind,
            'storage_key' => "test/{$caseId}/{$kind}.jpg",
            'mime_type' => 'image/jpeg',
            'size_bytes' => 102400,
        ]);
    }

    // Simulated provider evidence.

    DB::table('provider_match_results')->insert([
        'case_id' => $caseId,
        'provider' => 'mock',
        'outcome' => 'matched',
        'is_simulated' => true,
        'checked_at' => now()->subMinutes(3),
    ]);

    // Manual review.

    DB::table('verification_reviews')->insert([
        'case_id' => $caseId,
        'reviewer_user_id' => $reviewer->id,
        'decision' => 'approved',
        'reason' => null,
        'reviewed_at' => now(),
    ]);

    // Load related records.

    $case = IdentityVerificationCase::query()
        ->with([
            'user.role',
            'requestedProgram',
            'documents',
            'providerMatchResults',
            'reviews.reviewer.role',
        ])
        ->findOrFail($caseId);

    $grant = StaffPermissionGrant::query()
        ->with([
            'user',
            'permission',
            'programScope.program',
        ])
        ->findOrFail($grantId);

    $otp = OtpChallenge::findOrFail($otpId);

    // Account relationships.

    expect($case->user->id)
        ->toBe($student->id);

    expect($case->user->role->code)
        ->toBe('student');

    expect($case->requestedProgram->id)
        ->toBe($program->id);

    // Staff scope and permission relationships.

    expect($grant->user->id)
        ->toBe($reviewer->id);

    expect($grant->permission->code)
        ->toBe('identity.verification.review');

    expect($grant->programScope->user_id)
        ->toBe($reviewer->id);

    expect($grant->programScope->program->id)
        ->toBe($case->requested_program_id);

    // OTP lifecycle.

    expect($otp->user->id)
        ->toBe($student->id);

    expect($otp->consumed_at)
        ->not->toBeNull();

    expect($otp->secret_verifier)
        ->toBeNull();

    // Verification documents.

    expect($case->documents)
        ->toHaveCount(2);

    // Provider evidence.

    expect($case->providerMatchResults)
        ->toHaveCount(1);

    $providerResult = $case->providerMatchResults->first();

    expect($providerResult->outcome)
        ->toBe(ProviderMatchOutcome::Matched);

    expect($providerResult->is_simulated)
        ->toBeTrue();

    // Manual review.

    expect($case->reviews)
        ->toHaveCount(1);

    $review = $case->reviews->first();

    expect($review->reviewer->id)
        ->toBe($reviewer->id);

    expect($review->reviewer->role->code)
        ->toBe('education');

    expect($review->decision)
        ->toBe(VerificationReviewDecision::Approved);

    expect($reviewer->performedVerificationReviews()->count())
        ->toBe(1);

    // Evidence must not automatically finalize the case.

    expect($case->status)
        ->toBe(IdentityVerificationStatus::Submitted);

    expect($case->decided_at)
        ->toBeNull();

    // Sensitive data must remain hidden.

    expect($case->toArray())
        ->not->toHaveKey('national_id_ciphertext')
        ->not->toHaveKey('birth_date')
        ->not->toHaveKey('submitted_mobile');

    expect($case->documents->first()->toArray())
        ->not->toHaveKey('storage_key');

    expect($otp->toArray())
        ->not->toHaveKey('secret_verifier');

    expect($providerResult->toArray())
        ->not->toHaveKey('provider_reference');

    expect($review->toArray())
        ->not->toHaveKey('reason');
});
