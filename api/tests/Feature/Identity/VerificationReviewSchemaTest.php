<?php

use App\Enums\IdentityVerificationStatus;
use App\Enums\VerificationReviewDecision;
use App\Models\AcademicProgram;
use App\Models\IdentityVerificationCase;
use App\Models\Role;
use App\Models\User;
use App\Models\VerificationReview;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->student = User::factory()->create();

    $this->educationRole = Role::query()->firstOrCreate(
        ['code' => 'education'],
        ['name' => 'Education']
    );

    $this->reviewer = User::factory()
        ->forRole($this->educationRole)
        ->create();

    $this->program = AcademicProgram::create([
        'code' => 'TEST-CE-BSC',
        'name' => 'Computer Engineering',
        'degree_level' => 'bachelor',
    ]);

    $this->caseData = [
        'user_id' => $this->student->id,
        'requested_program_id' => $this->program->id,
        'national_id_ciphertext' => Crypt::encryptString('0012345678'),
        'birth_date' => '2000-01-01',
        'submitted_mobile' => $this->student->mobile,
        'status' => 'submitted',
        'submitted_at' => now()->subHour(),
    ];

    $this->caseId = DB::table('identity_verification_cases')
        ->insertGetId($this->caseData);

    $this->verificationCase = IdentityVerificationCase::findOrFail(
        $this->caseId
    );

    $this->reviewData = [
        'case_id' => $this->caseId,
        'reviewer_user_id' => $this->reviewer->id,
        'decision' => 'approved',
        'reason' => null,
        'reviewed_at' => now(),
    ];
});

test('verification reviews table has required columns', function () {
    expect(Schema::hasColumns('verification_reviews', [
        'id',
        'case_id',
        'reviewer_user_id',
        'decision',
        'reason',
        'reviewed_at',
    ]))->toBeTrue();
});

test('verification reviews do not have framework timestamps', function () {
    expect(Schema::hasColumn(
        'verification_reviews',
        'created_at'
    ))->toBeFalse();

    expect(Schema::hasColumn(
        'verification_reviews',
        'updated_at'
    ))->toBeFalse();
});

test('valid verification review decisions can be stored', function (
    string $decision
) {
    $id = DB::table('verification_reviews')->insertGetId([
        ...$this->reviewData,
        'decision' => $decision,
        'reason' => 'Test review reason',
    ]);

    expect(VerificationReview::findOrFail($id)->decision)->toBe(VerificationReviewDecision::from($decision));
})->with([
    'approved',
    'rejected',
]);

test('unsupported review decisions are rejected', function (
    string $decision
) {
    expect(fn () => DB::table('verification_reviews')->insert([
        ...$this->reviewData,
        'decision' => $decision,
    ]))->toThrow(QueryException::class, 'SQLSTATE[23514]');
})->with([
    '',
    'pending',
    'submitted',
    'matched',
    'invalid_decision',
]);

test('required review columns cannot be null', function (
    string $column
) {
    expect(fn () => DB::table('verification_reviews')->insert([
        ...$this->reviewData,
        $column => null,
    ]))->toThrow(QueryException::class, 'SQLSTATE[23502]');
})->with([
    'case_id',
    'reviewer_user_id',
    'decision',
    'reviewed_at',
]);

test('approved reviews do not require a reason', function () {
    $id = DB::table('verification_reviews')->insertGetId(
        $this->reviewData
    );

    expect(VerificationReview::findOrFail($id)->reason)
        ->toBeNull();
});

test('approved reviews can contain a reason', function () {
    $id = DB::table('verification_reviews')->insertGetId([
        ...$this->reviewData,
        'reason' => 'Documents verified',
    ]);

    expect(VerificationReview::findOrFail($id)->reason)
        ->toBe('Documents verified');
});

test('rejected reviews require a reason', function () {
    expect(fn () => DB::table('verification_reviews')->insert([
        ...$this->reviewData,
        'decision' => 'rejected',
        'reason' => null,
    ]))->toThrow(QueryException::class, 'SQLSTATE[23514]');
});

test('rejected reviews with valid reasons can be stored', function () {
    $id = DB::table('verification_reviews')->insertGetId([
        ...$this->reviewData,
        'decision' => 'rejected',
        'reason' => 'Unreadable diploma image',
    ]);

    $review = VerificationReview::findOrFail($id);

    expect($review->decision)
        ->toBe(VerificationReviewDecision::Rejected);

    expect($review->reason)
        ->toBe('Unreadable diploma image');
});

test('blank review reasons are rejected', function (
    string $decision,
    string $reason
) {
    expect(fn () => DB::table('verification_reviews')->insert([
        ...$this->reviewData,
        'decision' => $decision,
        'reason' => $reason,
    ]))->toThrow(QueryException::class, 'SQLSTATE[23514]');
})->with([
    'approved',
    'rejected',
])->with([
    '',
    '   ',
    "\t",
    "\n",
]);

test('review must reference an existing verification case', function () {
    expect(fn () => DB::table('verification_reviews')->insert([
        ...$this->reviewData,
        'case_id' => -1,
    ]))->toThrow(QueryException::class, 'SQLSTATE[23503]');
});

test('review must reference an existing reviewer', function () {
    expect(fn () => DB::table('verification_reviews')->insert([
        ...$this->reviewData,
        'reviewer_user_id' => -1,
    ]))->toThrow(QueryException::class, 'SQLSTATE[23503]');
});

test('reviewed at must be explicitly provided', function () {
    $data = $this->reviewData;

    unset($data['reviewed_at']);

    expect(fn () => DB::table('verification_reviews')->insert($data))
        ->toThrow(QueryException::class, 'SQLSTATE[23502]');
});

test('verification cases can contain multiple reviews', function () {
    DB::table('verification_reviews')->insert([
        ...$this->reviewData,
        'decision' => 'rejected',
        'reason' => 'Unreadable document',
    ]);

    DB::table('verification_reviews')->insert(
        $this->reviewData
    );

    expect($this->verificationCase->reviews()->count())
        ->toBe(2);
});

test('repeated reviews from the same reviewer are preserved', function () {
    DB::table('verification_reviews')->insert(
        $this->reviewData
    );

    DB::table('verification_reviews')->insert(
        $this->reviewData
    );

    expect($this->verificationCase->reviews()->count())
        ->toBe(2);

    expect($this->reviewer->performedVerificationReviews()->count())
        ->toBe(2);
});

test('a case can contain reviews from different reviewers', function () {
    $secondReviewer = User::factory()
        ->forRole($this->educationRole)
        ->create();

    DB::table('verification_reviews')->insert(
        $this->reviewData
    );

    DB::table('verification_reviews')->insert([
        ...$this->reviewData,
        'reviewer_user_id' => $secondReviewer->id,
    ]);

    expect($this->verificationCase->reviews()->count())
        ->toBe(2);

    expect($this->reviewer->performedVerificationReviews()->count())
        ->toBe(1);

    expect($secondReviewer->performedVerificationReviews()->count())
        ->toBe(1);
});

test('verification cases referenced by reviews cannot be deleted', function () {
    DB::table('verification_reviews')->insert(
        $this->reviewData
    );

    expect(fn () => $this->verificationCase->delete())
        ->toThrow(QueryException::class, 'SQLSTATE[23503]');
});

test('users referenced by reviews cannot be deleted', function () {
    DB::table('verification_reviews')->insert(
        $this->reviewData
    );

    expect(fn () => $this->reviewer->delete())
        ->toThrow(QueryException::class, 'SQLSTATE[23503]');
});

test('verification review relationships work correctly', function () {
    $id = DB::table('verification_reviews')->insertGetId(
        $this->reviewData
    );

    $review = VerificationReview::findOrFail($id);

    expect($review->verificationCase->id)
        ->toBe($this->caseId);

    expect($review->reviewer->id)
        ->toBe($this->reviewer->id);

    expect($this->verificationCase->reviews()->first()->id)
        ->toBe($review->id);

    expect($this->reviewer->performedVerificationReviews()->first()->id)
        ->toBe($review->id);
});

test('reviews are scoped to their verification case', function () {
    $secondCaseId = DB::table('identity_verification_cases')
        ->insertGetId($this->caseData);

    DB::table('verification_reviews')->insert(
        $this->reviewData
    );

    DB::table('verification_reviews')->insert([
        ...$this->reviewData,
        'case_id' => $secondCaseId,
    ]);

    expect($this->verificationCase->reviews()->count())
        ->toBe(1);

    expect($this->reviewer->performedVerificationReviews()->count())
        ->toBe(2);

    expect(DB::table('verification_reviews')->count())
        ->toBe(2);
});

test('verification reviews can be retrieved in chronological order', function () {
    $olderReviewId = DB::table('verification_reviews')->insertGetId([
        ...$this->reviewData,
        'decision' => 'rejected',
        'reason' => 'Invalid document',
        'reviewed_at' => now()->subMinutes(10),
    ]);

    $newerReviewId = DB::table('verification_reviews')->insertGetId(
        $this->reviewData
    );

    $reviews = $this->verificationCase
        ->reviews()
        ->orderByDesc('reviewed_at')
        ->get();

    expect($reviews->pluck('id')->all())
        ->toBe([
            $newerReviewId,
            $olderReviewId,
        ]);
});

test('verification review attributes are cast correctly', function () {
    $id = DB::table('verification_reviews')->insertGetId(
        $this->reviewData
    );

    $review = VerificationReview::findOrFail($id);

    expect($review->decision)->toBe(VerificationReviewDecision::Approved);

    expect($review->reviewed_at)->toBeInstanceOf(CarbonImmutable::class);
});

test('review model can be saved without framework timestamps', function () {
    $review = new VerificationReview();

    $review->case_id = $this->caseId;
    $review->reviewer_user_id = $this->reviewer->id;
    $review->decision = VerificationReviewDecision::Approved;
    $review->reviewed_at = now();

    $review->save();

    $review = $review->fresh();

    expect($review->decision)
        ->toBe(VerificationReviewDecision::Approved);

    expect($review->reviewed_at)
        ->toBeInstanceOf(CarbonImmutable::class);

    expect($review->getAttributes())
        ->not->toHaveKey('created_at')
        ->not->toHaveKey('updated_at');
});

test('review reasons are hidden during serialization', function () {
    $id = DB::table('verification_reviews')->insertGetId([
        ...$this->reviewData,
        'reason' => 'Internal review note',
    ]);

    $review = VerificationReview::findOrFail($id);

    expect($review->reason)->toBe('Internal review note');

    expect($review->toArray())->not->toHaveKey('reason');

    expect(json_decode($review->toJson(), true))->not->toHaveKey('reason');
});

test('review attributes are protected from mass assignment', function () {
    expect(fn () => new VerificationReview($this->reviewData))
        ->toThrow(MassAssignmentException::class);
});

test('verification reviews have the required indexes', function () {
    expect(Schema::hasIndex(
        'verification_reviews',
        'vr_case_reviewed_at_index'
    ))->toBeTrue();

    expect(Schema::hasIndex(
        'verification_reviews',
        'vr_reviewer_user_index'
    ))->toBeTrue();
});

test('storing a review does not finalize the verification case', function (
    string $decision
) {
    DB::table('verification_reviews')->insert([
        ...$this->reviewData,
        'decision' => $decision,
        'reason' => 'Test review reason',
    ]);

    $case = $this->verificationCase->fresh();

    expect($case->status)->toBe(IdentityVerificationStatus::Submitted);

    expect($case->decided_at)->toBeNull();
})->with([
    'approved',
    'rejected',
]);
