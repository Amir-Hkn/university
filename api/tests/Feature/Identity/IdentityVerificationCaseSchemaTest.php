<?php

use App\Enums\IdentityVerificationStatus;
use App\Models\AcademicProgram;
use App\Models\IdentityVerificationCase;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->program = AcademicProgram::create([
        'code' => 'TEST-CE-BSC',
        'name' => 'Computer Engineering',
        'degree_level' => 'bachelor',
    ]);

    $this->submittedAt = now()->startOfSecond();

    $this->caseData = [
        'user_id' => $this->user->id,
        'requested_program_id' => $this->program->id,
        'national_id_ciphertext' => Crypt::encryptString('0012345678'),
        'birth_date' => '2000-01-01',
        'submitted_mobile' => $this->user->mobile,
        'status' => 'submitted',
        'submitted_at' => $this->submittedAt,
    ];
});

test('identity verification cases table has required columns', function () {
    expect(Schema::hasColumns('identity_verification_cases', [
        'id',
        'user_id',
        'requested_program_id',
        'national_id_ciphertext',
        'birth_date',
        'submitted_mobile',
        'status',
        'submitted_at',
        'decided_at',
        'created_at',
        'updated_at',
    ]))->toBeTrue();
});

test('incomplete drafts are allowed and status defaults to draft', function () {
    $id = DB::table('identity_verification_cases')->insertGetId([
        'user_id' => $this->user->id,
    ]);

    $case = IdentityVerificationCase::findOrFail($id);

    expect($case->status)->toBe(IdentityVerificationStatus::Draft);

    expect($case->requestedProgram)->toBeNull();
    expect($case->submitted_at)->toBeNull();
    expect($case->decided_at)->toBeNull();
});

test('a complete submitted case can be stored', function () {
    $id = DB::table('identity_verification_cases')->insertGetId($this->caseData);

    $case = IdentityVerificationCase::findOrFail($id);

    expect($case->status)->toBe(IdentityVerificationStatus::Submitted);
});

test('unsupported statuses are rejected', function () {
    expect(fn () => DB::table('identity_verification_cases')->insert([
        ...$this->caseData,
        'status' => 'invalid_status',
    ]))->toThrow(QueryException::class, 'SQLSTATE[23514]');
});

test('submitted cases require complete identity data', function (string $column) {
    expect(fn () => DB::table('identity_verification_cases')->insert([
        ...$this->caseData,
        $column => null,
    ]))->toThrow(QueryException::class, 'SQLSTATE[23514]');
})->with([
    'requested_program_id',
    'national_id_ciphertext',
    'birth_date',
    'submitted_mobile',
    'submitted_at',
]);

test('invalid mobile formats are rejected', function (string $mobile) {
    expect(fn () => DB::table('identity_verification_cases')->insert([
        ...$this->caseData,
        'submitted_mobile' => $mobile,
    ]))->toThrow(QueryException::class, 'SQLSTATE[23514]');
})->with([
    '',
    'invalid',
    '09123456789',
    '+0123456789',
]);

test('blank national id ciphertext is rejected', function (string $value) {
    expect(fn () => DB::table('identity_verification_cases')->insert([
        ...$this->caseData,
        'national_id_ciphertext' => $value,
    ]))->toThrow(QueryException::class, 'SQLSTATE[23514]');
})->with(['', '   ']);

test('draft cases cannot have submission or decision timestamps', function (string $column) {
    expect(fn () => DB::table('identity_verification_cases')->insert([
        'user_id' => $this->user->id,
        'status' => 'draft',
        $column => now(),
    ]))->toThrow(QueryException::class, 'SQLSTATE[23514]');
})->with([
    'submitted_at',
    'decided_at',
]);

test('submitted cases cannot have a decision timestamp', function () {
    expect(fn () => DB::table('identity_verification_cases')->insert([
        ...$this->caseData,
        'decided_at' => now(),
    ]))->toThrow(QueryException::class, 'SQLSTATE[23514]');
});

test('terminal statuses require a decision timestamp', function (string $status) {
    expect(fn () => DB::table('identity_verification_cases')->insert([
        ...$this->caseData,
        'status' => $status,
    ]))->toThrow(QueryException::class, 'SQLSTATE[23514]');
})->with([
    'approved',
    'rejected',
]);

test('terminal statuses require a submission timestamp', function (string $status) {
    expect(fn () => DB::table('identity_verification_cases')->insert([
        ...$this->caseData,
        'status' => $status,
        'submitted_at' => null,
        'decided_at' => now(),
    ]))->toThrow(QueryException::class, 'SQLSTATE[23514]');
})->with([
    'approved',
    'rejected',
]);

test('decisions cannot occur before submission', function () {
    expect(fn () => DB::table('identity_verification_cases')->insert([
        ...$this->caseData,
        'status' => 'approved',
        'decided_at' => $this->submittedAt->copy()->subSecond(),
    ]))->toThrow(QueryException::class, 'SQLSTATE[23514]');
});

test('valid terminal states can be stored', function (string $status) {
    $id = DB::table('identity_verification_cases')->insertGetId([
        ...$this->caseData,
        'status' => $status,
        'decided_at' => $this->submittedAt->copy()->addHour(),
    ]);

    expect(IdentityVerificationCase::findOrFail($id)->status)
        ->toBe(IdentityVerificationStatus::from($status));
})->with([
    'approved',
    'rejected',
]);

test('cases must reference an existing user and program', function (string $column) {
    expect(fn () => DB::table('identity_verification_cases')->insert([
        ...$this->caseData,
        $column => -1,
    ]))->toThrow(QueryException::class, 'SQLSTATE[23503]');
})->with([
    'user_id',
    'requested_program_id',
]);

test('cases require a user', function () {
    expect(fn () => DB::table('identity_verification_cases')->insert([
        'user_id' => null,
    ]))->toThrow(QueryException::class, 'SQLSTATE[23502]');
});

test('referenced users cannot be deleted', function () {
    DB::table('identity_verification_cases')->insert($this->caseData);

    expect(fn () => $this->user->delete())
        ->toThrow(QueryException::class, 'SQLSTATE[23503]');
});

test('referenced programs cannot be deleted', function () {
    DB::table('identity_verification_cases')->insert($this->caseData);

    expect(fn () => $this->program->delete())
        ->toThrow(QueryException::class, 'SQLSTATE[23503]');
});

test('a user can retain multiple historical verification cases', function () {
    DB::table('identity_verification_cases')->insert([
        [
            ...$this->caseData,
            'status' => 'rejected',
            'decided_at' => $this->submittedAt->copy()->addHour(),
        ],
        [
            ...$this->caseData,
            'decided_at' => null,
        ],
    ]);

    expect($this->user->identityVerificationCases()->count())->toBe(2);
});

test('identity verification relationships work correctly', function () {
    $id = DB::table('identity_verification_cases')
        ->insertGetId($this->caseData);

    $case = IdentityVerificationCase::findOrFail($id);

    expect($case->user->id)->toBe($this->user->id);

    expect($case->requestedProgram->id)
        ->toBe($this->program->id);

    expect($this->user->identityVerificationCases()->count())
        ->toBe(1);

    expect($this->program->identityVerificationCases()->count())
        ->toBe(1);
});

test('date and timestamp attributes are cast correctly', function () {
    $id = DB::table('identity_verification_cases')
        ->insertGetId($this->caseData);

    $case = IdentityVerificationCase::findOrFail($id);

    expect($case->birth_date)
        ->toBeInstanceOf(CarbonImmutable::class);

    expect($case->submitted_at)
        ->toBeInstanceOf(CarbonImmutable::class);
});

test('national id is encrypted at rest and decrypted by the model', function () {
    $case = new IdentityVerificationCase();

    $case->user_id = $this->user->id;
    $case->national_id_ciphertext = '0012345678';

    $case->save();

    $storedValue = DB::table('identity_verification_cases')
        ->where('id', $case->id)
        ->value('national_id_ciphertext');

    expect($storedValue)->not->toBe('0012345678');

    expect(Crypt::decryptString($storedValue))
        ->toBe('0012345678');

    expect($case->fresh()->national_id_ciphertext)
        ->toBe('0012345678');
});

test('sensitive identity attributes are hidden during serialization', function () {
    $id = DB::table('identity_verification_cases')
        ->insertGetId($this->caseData);

    $case = IdentityVerificationCase::findOrFail($id);

    foreach ([$case->toArray(), json_decode($case->toJson(), true)] as $data) {
        expect($data)
            ->not->toHaveKey('national_id_ciphertext')
            ->not->toHaveKey('birth_date')
            ->not->toHaveKey('submitted_mobile');
    }
});

test('case attributes are protected from mass assignment', function () {
    expect(fn () => new IdentityVerificationCase($this->caseData))
        ->toThrow(MassAssignmentException::class);
});

test('verification cases have a composite user history index', function () {
    expect(Schema::hasIndex(
        'identity_verification_cases',
        'ivc_user_created_at_index'
    ))->toBeTrue();

    expect(Schema::hasIndex(
        'identity_verification_cases',
        'identity_verification_cases_user_id_index'
    ))->toBeFalse();
});
