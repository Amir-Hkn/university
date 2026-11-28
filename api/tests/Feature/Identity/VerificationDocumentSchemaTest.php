<?php

use App\Enums\VerificationDocumentKind;
use App\Models\IdentityVerificationCase;
use App\Models\User;
use App\Models\VerificationDocument;
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

    $this->caseId = DB::table('identity_verification_cases')
        ->insertGetId([
            'user_id' => $this->user->id,
        ]);

    $this->verificationCase = IdentityVerificationCase::findOrFail(
        $this->caseId
    );

    $this->documentData = [
        'case_id' => $this->caseId,
        'kind' => 'national_id_image',
        'storage_key' => sprintf(
            'identity-verifications/%d/%s.jpg',
            $this->caseId,
            Str::uuid()
        ),
        'mime_type' => 'image/jpeg',
        'size_bytes' => 102400,
    ];
});

test('verification documents table has required columns', function () {
    expect(Schema::hasColumns('verification_documents', [
        'id',
        'case_id',
        'kind',
        'storage_key',
        'mime_type',
        'size_bytes',
        'created_at',
    ]))->toBeTrue();
});

test('verification documents do not have an updated at column', function () {
    expect(Schema::hasColumn(
        'verification_documents',
        'updated_at'
    ))->toBeFalse();
});

test('valid document kinds can be stored', function (string $kind) {
    $id = DB::table('verification_documents')->insertGetId([
        ...$this->documentData,
        'kind' => $kind,
    ]);

    expect(VerificationDocument::findOrFail($id)->kind)
        ->toBe(VerificationDocumentKind::from($kind));
})->with([
    'national_id_image',
    'latest_diploma_image',
]);

test('unsupported document kinds are rejected', function (string $kind) {
    expect(fn () => DB::table('verification_documents')->insert([
        ...$this->documentData,
        'kind' => $kind,
    ]))->toThrow(QueryException::class, 'SQLSTATE[23514]');
})->with([
    '',
    'passport',
    'profile_image',
    'invalid_kind',
]);

test('required document columns cannot be null', function (string $column) {
    expect(fn () => DB::table('verification_documents')->insert([
        ...$this->documentData,
        $column => null,
    ]))->toThrow(QueryException::class, 'SQLSTATE[23502]');
})->with([
    'case_id',
    'kind',
    'storage_key',
    'mime_type',
    'size_bytes',
    'created_at',
]);

test('document must reference an existing verification case', function () {
    expect(fn () => DB::table('verification_documents')->insert([
        ...$this->documentData,
        'case_id' => -1,
    ]))->toThrow(QueryException::class, 'SQLSTATE[23503]');
});

test('blank storage keys are rejected', function (string $storageKey) {
    expect(fn () => DB::table('verification_documents')->insert([
        ...$this->documentData,
        'storage_key' => $storageKey,
    ]))->toThrow(QueryException::class, 'SQLSTATE[23514]');
})->with([
    '',
    '   ',
    "\t",
    "\n",
]);

test('blank mime types are rejected', function (string $mimeType) {
    expect(fn () => DB::table('verification_documents')->insert([
        ...$this->documentData,
        'mime_type' => $mimeType,
    ]))->toThrow(QueryException::class, 'SQLSTATE[23514]');
})->with([
    '',
    '   ',
    "\t",
]);

test('document size must be greater than zero', function (int $size) {
    expect(fn () => DB::table('verification_documents')->insert([
        ...$this->documentData,
        'size_bytes' => $size,
    ]))->toThrow(QueryException::class, 'SQLSTATE[23514]');
})->with([
    0,
    -1,
    -102400,
]);

test('a document with a positive size can be stored', function () {
    $id = DB::table('verification_documents')->insertGetId([
        ...$this->documentData,
        'size_bytes' => 1,
    ]);

    expect(VerificationDocument::findOrFail($id)->size_bytes)
        ->toBe(1);
});

test('duplicate document kinds within the same case are rejected', function () {
    DB::table('verification_documents')->insert(
        $this->documentData
    );

    expect(fn () => DB::table('verification_documents')->insert([
        ...$this->documentData,
        'storage_key' => sprintf(
            'identity-verifications/%d/%s.jpg',
            $this->caseId,
            Str::uuid()
        ),
    ]))->toThrow(QueryException::class, 'SQLSTATE[23505]');
});

test('a case can contain both required document kinds', function () {
    DB::table('verification_documents')->insert(
        $this->documentData
    );

    DB::table('verification_documents')->insert([
        ...$this->documentData,
        'kind' => 'latest_diploma_image',
        'storage_key' => sprintf(
            'identity-verifications/%d/%s.jpg',
            $this->caseId,
            Str::uuid()
        ),
    ]);

    expect($this->verificationCase->documents()->count())
        ->toBe(2);
});

test('different cases can contain the same document kind', function () {
    $secondCaseId = DB::table('identity_verification_cases')
        ->insertGetId([
            'user_id' => $this->user->id,
        ]);

    DB::table('verification_documents')->insert(
        $this->documentData
    );

    DB::table('verification_documents')->insert([
        ...$this->documentData,
        'case_id' => $secondCaseId,
        'storage_key' => sprintf(
            'identity-verifications/%d/%s.jpg',
            $secondCaseId,
            Str::uuid()
        ),
    ]);

    expect(DB::table('verification_documents')->count())
        ->toBe(2);
});

test('verification cases referenced by documents cannot be deleted', function () {
    DB::table('verification_documents')->insert(
        $this->documentData
    );

    expect(fn () => $this->verificationCase->delete())
        ->toThrow(QueryException::class, 'SQLSTATE[23503]');
});

test('verification document relationships work correctly', function () {
    $id = DB::table('verification_documents')
        ->insertGetId($this->documentData);

    $document = VerificationDocument::findOrFail($id);

    expect($document->verificationCase->id)
        ->toBe($this->caseId);

    expect($this->verificationCase->documents()->count())
        ->toBe(1);

    expect($this->verificationCase->documents()->first()->id)
        ->toBe($document->id);
});

test('verification document attributes are cast correctly', function () {
    $id = DB::table('verification_documents')
        ->insertGetId($this->documentData);

    $document = VerificationDocument::findOrFail($id);

    expect($document->kind)
        ->toBe(VerificationDocumentKind::NationalIdImage);

    expect($document->size_bytes)
        ->toBeInt()
        ->toBe(102400);

    expect($document->created_at)
        ->toBeInstanceOf(CarbonImmutable::class);
});

test('created at defaults to the current timestamp', function () {
    $id = DB::table('verification_documents')
        ->insertGetId($this->documentData);

    expect(VerificationDocument::findOrFail($id)->created_at)
        ->not->toBeNull();
});

test('document model can be saved without an updated at column', function () {
    $document = new VerificationDocument();

    $document->case_id = $this->caseId;
    $document->kind = VerificationDocumentKind::NationalIdImage;
    $document->storage_key = $this->documentData['storage_key'];
    $document->mime_type = 'image/jpeg';
    $document->size_bytes = 102400;

    $document->save();

    expect($document->fresh()->created_at)
        ->toBeInstanceOf(CarbonImmutable::class);

    expect($document->fresh()->getAttributes())
        ->not->toHaveKey('updated_at');
});

test('storage key is hidden during serialization', function () {
    $id = DB::table('verification_documents')
        ->insertGetId($this->documentData);

    $document = VerificationDocument::findOrFail($id);

    expect($document->toArray())
        ->not->toHaveKey('storage_key');

    expect(json_decode($document->toJson(), true))
        ->not->toHaveKey('storage_key');
});

test('document attributes are protected from mass assignment', function () {
    expect(fn () => new VerificationDocument($this->documentData))
        ->toThrow(MassAssignmentException::class);
});
