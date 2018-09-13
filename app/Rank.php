<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Rank
 *
 * @package App
 */
class Rank extends Model
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function members()
    {
        return $this->hasMany(Member::class);
    }
}
