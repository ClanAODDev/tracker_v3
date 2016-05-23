<?php

namespace App;

use App\Division;
use App\User;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function subject()
    {
        return $this->morphTo();
    }

    public function division()
    {
        return $this->belongsTo(Division::class);
    }
}
