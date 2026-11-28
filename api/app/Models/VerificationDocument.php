<?php

namespace App\Models;

use App\Enums\VerificationDocumentKind;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificationDocument extends Model
{
    public const UPDATED_AT = null;

    protected $guarded = ['*'];

    protected $hidden = [
        'storage_key',
    ];

    protected function casts(): array
    {
        return [
            'kind' => VerificationDocumentKind::class,
            'size_bytes' => 'integer',
            'created_at' => 'immutable_datetime',
        ];
    }

    public function verificationCase(): BelongsTo
    {
        return $this->belongsTo(
            IdentityVerificationCase::class,
            'case_id'
        );
    }
}
