<?php

use App\Enums\ProviderMatchOutcome;
use App\Models\IdentityVerificationCase;
use App\Models\ProviderMatchResult;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->caseId = DB::table('identity_verification_cases')
        ->insertGetId([
            'user_id' => $this->user->id,
        ]);

    $this->verificationCase = IdentityVerificationCase::findOrFail(
        $this->caseId
    );

    $this->resultData = [
        'case_id' => $this->caseId,
        'provider' => 'api.ir',
        'outcome' => 'matched',
        'is_simulated' => false,
        'provider_reference' => 'test-reference-001',
        'checked_at' => now(),
    ];
});

test('provider match results table has required columns', function () {
    expect(Schema::hasColumns('provider_match_results', [
        'id',
        'case_id',
        'provider',
        'outcome',
        'is_simulated',
        'provider_reference',
        'checked_at',
    ]))->toBeTrue();
});

test('provider match results do not have framework timestamps', function () {
    expect(Schema::hasColumn(
        'provider_match_results',
        'created_at'
    ))->toBeFalse();

    expect(Schema::hasColumn(
        'provider_match_results',
        'updated_at'
    ))->toBeFalse();
});

test('valid provider outcomes can be stored', function (string $outcome) {
    $id = DB::table('provider_match_results')->insertGetId([
        ...$this->resultData,
        'outcome' => $outcome,
    ]);

    expect(ProviderMatchResult::findOrFail($id)->outcome)
        ->toBe(ProviderMatchOutcome::from($outcome));
})->with([
    'matched',
    'mismatched',
    'unavailable',
    'error',
]);

test('unsupported provider outcomes are rejected', function (string $outcome) {
    expect(fn () => DB::table('provider_match_results')->insert([
        ...$this->resultData,
        'outcome' => $outcome,
    ]))->toThrow(QueryException::class, 'SQLSTATE[23514]');
})->with([
    '',
    'pending',
    'approved',
    'rejected',
    'invalid_outcome',
]);

test('required result columns cannot be null', function (string $column) {
    expect(fn () => DB::table('provider_match_results')->insert([
        ...$this->resultData,
        $column => null,
    ]))->toThrow(QueryException::class, 'SQLSTATE[23502]');
})->with([
    'case_id',
    'provider',
    'outcome',
    'is_simulated',
    'checked_at',
]);

test('provider reference is optional', function () {
    $id = DB::table('provider_match_results')->insertGetId([
        ...$this->resultData,
        'provider_reference' => null,
    ]);

    expect(ProviderMatchResult::findOrFail($id)->provider_reference)
        ->toBeNull();
});

test('blank provider names are rejected', function (string $provider) {
    expect(fn () => DB::table('provider_match_results')->insert([
        ...$this->resultData,
        'provider' => $provider,
    ]))->toThrow(QueryException::class, 'SQLSTATE[23514]');
})->with([
    '',
    '   ',
    "\t",
    "\n",
]);

test('blank provider references are rejected', function (string $reference) {
    expect(fn () => DB::table('provider_match_results')->insert([
        ...$this->resultData,
        'provider_reference' => $reference,
    ]))->toThrow(QueryException::class, 'SQLSTATE[23514]');
})->with([
    '',
    '   ',
    "\t",
    "\n",
]);

test('result must reference an existing verification case', function () {
    expect(fn () => DB::table('provider_match_results')->insert([
        ...$this->resultData,
        'case_id' => -1,
    ]))->toThrow(QueryException::class, 'SQLSTATE[23503]');
});

test('simulation status must be explicitly provided', function () {
    $data = $this->resultData;

    unset($data['is_simulated']);

    expect(fn () => DB::table('provider_match_results')->insert($data))
        ->toThrow(QueryException::class, 'SQLSTATE[23502]');
});

test('checked at must be explicitly provided', function () {
    $data = $this->resultData;

    unset($data['checked_at']);

    expect(fn () => DB::table('provider_match_results')->insert($data))
        ->toThrow(QueryException::class, 'SQLSTATE[23502]');
});

test('real and simulated results can be distinguished', function (bool $isSimulated) {
    $id = DB::table('provider_match_results')->insertGetId([
        ...$this->resultData,
        'is_simulated' => $isSimulated,
    ]);

    expect(ProviderMatchResult::findOrFail($id)->is_simulated)
        ->toBe($isSimulated);
})->with([
    true,
    false,
]);

test('a verification case can contain multiple provider results', function () {
    DB::table('provider_match_results')->insert([
        ...$this->resultData,
        'outcome' => 'unavailable',
    ]);

    DB::table('provider_match_results')->insert([
        ...$this->resultData,
        'outcome' => 'error',
    ]);

    DB::table('provider_match_results')->insert([
        ...$this->resultData,
        'outcome' => 'matched',
    ]);

    expect($this->verificationCase->providerMatchResults()->count())
        ->toBe(3);
});

test('repeated outcomes from the same provider are preserved', function () {
    DB::table('provider_match_results')->insert(
        $this->resultData
    );

    DB::table('provider_match_results')->insert(
        $this->resultData
    );

    expect($this->verificationCase->providerMatchResults()->count())
        ->toBe(2);
});

test('a verification case can contain results from different providers', function () {
    DB::table('provider_match_results')->insert(
        $this->resultData
    );

    DB::table('provider_match_results')->insert([
        ...$this->resultData,
        'provider' => 'mock',
        'is_simulated' => true,
        'provider_reference' => null,
    ]);

    expect($this->verificationCase->providerMatchResults()->count())
        ->toBe(2);
});

test('verification cases referenced by provider results cannot be deleted', function () {
    DB::table('provider_match_results')->insert(
        $this->resultData
    );

    expect(fn () => $this->verificationCase->delete())
        ->toThrow(QueryException::class, 'SQLSTATE[23503]');
});

test('provider match result relationships work correctly', function () {
    $id = DB::table('provider_match_results')->insertGetId(
        $this->resultData
    );

    $result = ProviderMatchResult::findOrFail($id);

    expect($result->verificationCase->id)
        ->toBe($this->caseId);

    expect($this->verificationCase->providerMatchResults()->count())
        ->toBe(1);

    expect($this->verificationCase->providerMatchResults()->first()->id)
        ->toBe($result->id);
});

test('provider results are scoped to their verification case', function () {
    $secondCaseId = DB::table('identity_verification_cases')
        ->insertGetId([
            'user_id' => $this->user->id,
        ]);

    DB::table('provider_match_results')->insert(
        $this->resultData
    );

    DB::table('provider_match_results')->insert([
        ...$this->resultData,
        'case_id' => $secondCaseId,
    ]);

    expect($this->verificationCase->providerMatchResults()->count())
        ->toBe(1);

    expect(DB::table('provider_match_results')->count())
        ->toBe(2);
});

test('provider results can be retrieved in chronological order', function () {
    $olderResultId = DB::table('provider_match_results')->insertGetId([
        ...$this->resultData,
        'outcome' => 'error',
        'checked_at' => now()->subMinutes(10),
    ]);

    $newerResultId = DB::table('provider_match_results')->insertGetId([
        ...$this->resultData,
        'checked_at' => now(),
    ]);

    $results = $this->verificationCase
        ->providerMatchResults()
        ->orderByDesc('checked_at')
        ->get();

    expect($results->pluck('id')->all())
        ->toBe([
            $newerResultId,
            $olderResultId,
        ]);
});

test('provider match result attributes are cast correctly', function () {
    $id = DB::table('provider_match_results')->insertGetId(
        $this->resultData
    );

    $result = ProviderMatchResult::findOrFail($id);

    expect($result->outcome)
        ->toBe(ProviderMatchOutcome::Matched);

    expect($result->is_simulated)
        ->toBeFalse();

    expect($result->checked_at)
        ->toBeInstanceOf(CarbonImmutable::class);
});

test('provider result model can be saved without framework timestamps', function () {
    $result = new ProviderMatchResult();

    $result->case_id = $this->caseId;
    $result->provider = 'mock';
    $result->outcome = ProviderMatchOutcome::Matched;
    $result->is_simulated = true;
    $result->checked_at = now();

    $result->save();

    expect($result->fresh()->outcome)
        ->toBe(ProviderMatchOutcome::Matched);

    expect($result->fresh()->getAttributes())
        ->not->toHaveKey('created_at')
        ->not->toHaveKey('updated_at');
});

test('provider reference is hidden during serialization', function () {
    $id = DB::table('provider_match_results')->insertGetId(
        $this->resultData
    );

    $result = ProviderMatchResult::findOrFail($id);

    expect($result->toArray())
        ->not->toHaveKey('provider_reference');

    expect(json_decode($result->toJson(), true))
        ->not->toHaveKey('provider_reference');
});

test('provider result attributes are protected from mass assignment', function () {
    expect(fn () => new ProviderMatchResult($this->resultData))
        ->toThrow(MassAssignmentException::class);
});

test('provider results have a case and checked at index', function () {
    expect(Schema::hasIndex(
        'provider_match_results',
        'pmr_case_checked_at_index'
    ))->toBeTrue();
});

test('storing a matched result does not approve the verification case', function () {
    DB::table('provider_match_results')->insert(
        $this->resultData
    );

    expect($this->verificationCase->fresh()->status->value)
        ->toBe('draft');
});
