<?php

namespace App\Models;

use App\Enums\VerificationReviewDecision;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificationReview extends Model
{
    public $timestamps = false;

    protected $guarded = ['*'];

    protected $hidden = [
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'decision' => VerificationReviewDecision::class,
            'reviewed_at' => 'immutable_datetime',
        ];
    }

    public function verificationCase(): BelongsTo
    {
        return $this->belongsTo(IdentityVerificationCase::class, 'case_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_user_id');
    }
}
