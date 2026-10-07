<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use SoftDeletes;

    public const LEGACY_PLATOON = 'platoon';

    public const LEGACY_SQUAD = 'squad';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'gen_pop' => 'boolean',
        ];
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    public function leader(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'leader_id', 'clan_id');
    }

    public function isPlatoon(): bool
    {
        return $this->legacy_type === self::LEGACY_PLATOON;
    }

    public function isSquad(): bool
    {
        return $this->legacy_type === self::LEGACY_SQUAD;
    }
}
