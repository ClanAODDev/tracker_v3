<?php

namespace App\Models;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbilityAudit extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['actor_id', 'action', 'role', 'user_id', 'ability', 'reason'];

    protected function casts(): array
    {
        return ['role' => Role::class];
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
