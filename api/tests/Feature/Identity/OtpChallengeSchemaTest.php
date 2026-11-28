<?php

use App\Enums\OtpPurpose;
use App\Models\OtpChallenge;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->issuedAt = now()->startOfSecond();

    $this->otpData = [
        'user_id' => null,
        'destination' => '+12025550199',
        'purpose' => 'registration',
        'context_id' => (string) Str::uuid(),
        'secret_verifier' => str_repeat('a', 64),
        'issued_at' => $this->issuedAt,
        'expires_at' => $this->issuedAt->copy()->addMinutes(2),
    ];
});

test('otp challenges table has required columns', function () {
    expect(Schema::hasColumns('otp_challenges', [
        'id',
        'user_id',
        'destination',
        'purpose',
        'context_id',
        'secret_verifier',
        'attempts',
        'issued_at',
        'expires_at',
        'consumed_at',
        'invalidated_at',
        'created_at',
        'updated_at',
    ]))->toBeTrue();
});

test('registration challenge can exist without a user', function () {
    DB::table('otp_challenges')->insert($this->otpData);

    $this->assertDatabaseHas('otp_challenges', [
        'user_id' => null,
        'purpose' => 'registration',
    ]);
});

test('login challenge can belong to a user', function () {
    DB::table('otp_challenges')->insert([
        ...$this->otpData,
        'purpose' => 'login',
        'user_id' => $this->user->id,
        'destination' => $this->user->mobile,
    ]);

    $this->assertDatabaseHas('otp_challenges', [
        'purpose' => 'login',
        'user_id' => $this->user->id,
    ]);
});

test('login challenge requires a user', function () {
    expect(fn () => DB::table('otp_challenges')->insert([
        ...$this->otpData,
        'purpose' => 'login',
    ]))->toThrow(QueryException::class, 'SQLSTATE[23514]');
});

test('challenge must reference an existing user when provided', function () {
    expect(fn () => DB::table('otp_challenges')->insert([
        ...$this->otpData,
        'user_id' => -1,
    ]))->toThrow(QueryException::class, 'SQLSTATE[23503]');
});

test('unsupported purposes are rejected', function () {
    expect(fn () => DB::table('otp_challenges')->insert([
        ...$this->otpData,
        'purpose' => 'invalid_purpose',
    ]))->toThrow(QueryException::class, 'SQLSTATE[23514]');
});

test('invalid destinations are rejected', function (string $destination) {
    expect(fn () => DB::table('otp_challenges')->insert([
        ...$this->otpData,
        'destination' => $destination,
    ]))->toThrow(QueryException::class, 'SQLSTATE[23514]');
})->with([
    '',
    'invalid',
    '09123456789',
    '+0123456789',
]);

test('invalid verifier formats are rejected', function (string $verifier) {
    expect(fn () => DB::table('otp_challenges')->insert([
        ...$this->otpData,
        'secret_verifier' => $verifier,
    ]))->toThrow(QueryException::class, 'SQLSTATE[23514]');
})->with([
    '',
    '123456',
    str_repeat('g', 64),
]);

test('attempts default to zero', function () {
    $id = DB::table('otp_challenges')->insertGetId($this->otpData);

    expect(OtpChallenge::findOrFail($id)->attempts)->toBe(0);
});

test('negative attempts are rejected', function () {
    expect(fn () => DB::table('otp_challenges')->insert([
        ...$this->otpData,
        'attempts' => -1,
    ]))->toThrow(QueryException::class, 'SQLSTATE[23514]');
});

test('invalid expiration times are rejected', function (int $offset) {
    expect(fn () => DB::table('otp_challenges')->insert([
        ...$this->otpData,
        'expires_at' => $this->issuedAt->copy()->addSeconds($offset),
    ]))->toThrow(QueryException::class, 'SQLSTATE[23514]');
})->with([0, -60]);

test('required columns cannot be null', function (string $column) {
    expect(fn () => DB::table('otp_challenges')->insert([
        ...$this->otpData,
        $column => null,
    ]))->toThrow(QueryException::class, 'SQLSTATE[23502]');
})->with([
    'destination',
    'purpose',
    'context_id',
    'attempts',
    'issued_at',
    'expires_at',
]);

test('challenge cannot be consumed and invalidated simultaneously', function () {
    expect(fn () => DB::table('otp_challenges')->insert([
        ...$this->otpData,
        'secret_verifier' => null,
        'consumed_at' => $this->issuedAt->copy()->addSeconds(30),
        'invalidated_at' => $this->issuedAt->copy()->addSeconds(40),
    ]))->toThrow(QueryException::class, 'SQLSTATE[23514]');
});

test('consumption outside the validity window is rejected', function (int $offset) {
    expect(fn () => DB::table('otp_challenges')->insert([
        ...$this->otpData,
        'secret_verifier' => null,
        'consumed_at' => $this->issuedAt->copy()->addSeconds($offset),
    ]))->toThrow(QueryException::class, 'SQLSTATE[23514]');
})->with([-1, 120, 121]);

test('consumption within the validity window is accepted', function () {
    $id = DB::table('otp_challenges')->insertGetId([
        ...$this->otpData,
        'secret_verifier' => null,
        'consumed_at' => $this->issuedAt->copy()->addSeconds(30),
    ]);

    expect(OtpChallenge::findOrFail($id)->consumed_at)->not->toBeNull();
});

test('invalidation before issuance is rejected', function () {
    expect(fn () => DB::table('otp_challenges')->insert([
        ...$this->otpData,
        'secret_verifier' => null,
        'invalidated_at' => $this->issuedAt->copy()->subSecond(),
    ]))->toThrow(QueryException::class, 'SQLSTATE[23514]');
});

test('challenge can be invalidated', function () {
    $id = DB::table('otp_challenges')->insertGetId([
        ...$this->otpData,
        'secret_verifier' => null,
        'invalidated_at' => $this->issuedAt->copy()->addSeconds(60),
    ]);

    expect(OtpChallenge::findOrFail($id)->invalidated_at)->not->toBeNull();
});

test('multiple challenges can share the same context', function () {
    DB::table('otp_challenges')->insert([
        $this->otpData,
        $this->otpData,
    ]);

    expect(DB::table('otp_challenges')
        ->where('context_id', $this->otpData['context_id'])
        ->count())->toBe(2);
});

test('referenced users cannot be deleted', function () {
    DB::table('otp_challenges')->insert([
        ...$this->otpData,
        'user_id' => $this->user->id,
    ]);

    expect(fn () => $this->user->delete())
        ->toThrow(QueryException::class, 'SQLSTATE[23503]');
});

test('otp challenge relationships work correctly', function () {
    $id = DB::table('otp_challenges')->insertGetId([
        ...$this->otpData,
        'user_id' => $this->user->id,
    ]);

    $challenge = OtpChallenge::findOrFail($id);

    expect($challenge->user->id)->toBe($this->user->id);

    expect($this->user->otpChallenges()->count())->toBe(1);
});

test('registration challenge relationship can be null', function () {
    $id = DB::table('otp_challenges')->insertGetId($this->otpData);

    expect(OtpChallenge::findOrFail($id)->user)->toBeNull();
});

test('otp challenge attributes are cast correctly', function () {
    $id = DB::table('otp_challenges')->insertGetId($this->otpData);

    $challenge = OtpChallenge::findOrFail($id);

    expect($challenge->purpose)->toBe(OtpPurpose::Registration);

    expect($challenge->attempts)->toBeInt();

    expect($challenge->issued_at)->toBeInstanceOf(CarbonImmutable::class);

    expect($challenge->expires_at)->toBeInstanceOf(CarbonImmutable::class);
});

test('secret verifier is hidden during serialization', function () {
    $id = DB::table('otp_challenges')->insertGetId($this->otpData);

    $challenge = OtpChallenge::findOrFail($id);

    expect($challenge->toArray())
        ->not->toHaveKey('secret_verifier');

    expect(json_decode($challenge->toJson(), true))
        ->not->toHaveKey('secret_verifier');
});

test('otp challenge attributes are protected from mass assignment', function () {
    expect(fn () => new OtpChallenge($this->otpData))
        ->toThrow(MassAssignmentException::class);
});


test('non terminal challenges require a secret verifier', function () {
    expect(fn () => DB::table('otp_challenges')->insert([
        ...$this->otpData,
        'secret_verifier' => null,
    ]))->toThrow(
        QueryException::class,
        'otp_verifier_lifecycle_check'
    );
});

test('terminal challenges cannot retain their verifier', function (
    string $terminalColumn
) {
    expect(fn () => DB::table('otp_challenges')->insert([
        ...$this->otpData,
        $terminalColumn => $this->issuedAt->copy()->addSeconds(30),
    ]))->toThrow(
        QueryException::class,
        'otp_verifier_lifecycle_check'
    );
})->with([
    'consumed_at',
    'invalidated_at',
]);

test('terminal transition requires clearing the verifier', function (
    string $terminalColumn
) {
    $id = DB::table('otp_challenges')->insertGetId(
        $this->otpData
    );

    expect(fn () => DB::table('otp_challenges')
        ->where('id', $id)
        ->update([
            $terminalColumn => $this->issuedAt->copy()->addSeconds(30),
        ]))
        ->toThrow(
            QueryException::class,
            'otp_verifier_lifecycle_check'
        );
})->with([
    'consumed_at',
    'invalidated_at',
]);

test('verifier can be cleared with a terminal transition', function (
    string $terminalColumn
) {
    $id = DB::table('otp_challenges')->insertGetId(
        $this->otpData
    );

    DB::table('otp_challenges')
        ->where('id', $id)
        ->update([
            'secret_verifier' => null,
            $terminalColumn => $this->issuedAt->copy()->addSeconds(30),
        ]);

    $challenge = OtpChallenge::findOrFail($id);

    expect($challenge->secret_verifier)->toBeNull();
    expect($challenge->{$terminalColumn})->not->toBeNull();
})->with([
    'consumed_at',
    'invalidated_at',
]);

test('a cleared verifier cannot be restored on a terminal challenge', function () {
    $id = DB::table('otp_challenges')->insertGetId([
        ...$this->otpData,
        'secret_verifier' => null,
        'consumed_at' => $this->issuedAt->copy()->addSeconds(30),
    ]);

    expect(fn () => DB::table('otp_challenges')
        ->where('id', $id)
        ->update([
            'secret_verifier' => str_repeat('b', 64),
        ]))
        ->toThrow(
            QueryException::class,
            'otp_verifier_lifecycle_check'
        );
});
