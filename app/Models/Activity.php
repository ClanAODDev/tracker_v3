<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Activity extends Model
{
    protected $guarded = [];

    protected $with = [
        'user',
        'division',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return MorphTo
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
