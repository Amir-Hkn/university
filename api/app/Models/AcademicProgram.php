<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable('code', 'name', 'degree_level')]
class AcademicProgram extends Model
{
    public function identityVerificationCases(): HasMany
    {
        return $this->hasMany(
            IdentityVerificationCase::class,
            'requested_program_id'
        );
    }
}
