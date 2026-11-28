<?php

namespace App\Models;

use App\Enums\IdentityVerificationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IdentityVerificationCase extends Model
{
    protected $guarded = ['*'];

    protected $hidden = [
        'national_id_ciphertext',
        'birth_date',
        'submitted_mobile',
    ];

    protected $attributes = [
        'status' => 'draft',
    ];

    protected function casts(): array
    {
        return [
            'national_id_ciphertext' => 'encrypted',
            'birth_date' => 'immutable_date',
            'status' => IdentityVerificationStatus::class,
            'submitted_at' => 'immutable_datetime',
            'decided_at' => 'immutable_datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function requestedProgram(): BelongsTo
    {
        return $this->belongsTo(AcademicProgram::class, 'requested_program_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(
            VerificationDocument::class,
            'case_id'
        );
    }
}
