<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StaffProgramScope extends Model
{
    protected $guarded = ['*'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(AcademicProgram::class, 'program_id');
    }

    public function permissionGrants(): HasMany
    {
        return $this->hasMany(StaffPermissionGrant::class);
    }
}
