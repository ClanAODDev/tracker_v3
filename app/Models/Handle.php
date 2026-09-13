<?php

namespace App\Models;

use App\Models\Handle\HasCustomAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Handle extends Model
{
    use HasCustomAttributes;
    use HasFactory;

    protected $casts = [
        'visible' => 'boolean',
        'enabled' => 'boolean',
    ];

    protected $guarded = [];

    public function divisions(): HasMany
    {
        return $this->hasMany(Division::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * Whether a handle value satisfies this type's format, if one is set.
     * A blank value or a type without a regex is always considered valid —
     * required-ness is a separate concern handled by the caller's rules.
     */
    public function matches(?string $value): bool
    {
        if (! $this->regex || $value === null || $value === '') {
            return true;
        }

        return @preg_match($this->regex, $value) === 1;
    }
}
