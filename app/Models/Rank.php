<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class Rank.
 */
class Rank extends Model
{
    /**
     * @return BelongsToMany
     */
    public function members()
    {
        return $this->hasMany(Member::class);
    }

    public function recommendation(): MorphOne
    {
        return $this->morphOne(Recommendation::class, 'recommendable');
    }
}
