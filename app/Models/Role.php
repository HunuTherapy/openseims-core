<?php

namespace App\Models;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use Auditable;

    public function supervisorRole(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'supervisor_role_id');
    }

    public function subordinateRoles(): HasMany
    {
        return $this->hasMany(Role::class, 'supervisor_role_id');
    }
}
