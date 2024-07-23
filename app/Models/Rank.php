<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class Rank.
 */
class Rank extends Model
{
    protected $guarded = [];

    public function members(): BelongsToMany
    {
        return $this->hasMany(Member::class);
    }
}
