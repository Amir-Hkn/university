<?php

namespace App\Models;

use App\Enums\ProviderMatchOutcome;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderMatchResult extends Model
{
    public $timestamps = false;

    protected $guarded = ['*'];

    protected $hidden = [
        'provider_reference',
    ];

    protected function casts(): array
    {
        return [
            'outcome' => ProviderMatchOutcome::class,
            'is_simulated' => 'boolean',
            'checked_at' => 'immutable_datetime',
        ];
    }

    public function verificationCase(): BelongsTo
    {
        return $this->belongsTo(IdentityVerificationCase::class, 'case_id');
    }
}
