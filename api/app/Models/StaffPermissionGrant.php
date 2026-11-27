<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffPermissionGrant extends Model
{
    protected $guarded = ['*'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class);
    }

    public function programScope(): BelongsTo
    {
        return $this->belongsTo(StaffProgramScope::class, 'staff_program_scope_id');
    }
}
