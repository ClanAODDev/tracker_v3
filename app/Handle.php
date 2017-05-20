<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Handle extends Model
{
    protected $casts = [
        'visible' => 'boolean',
    ];

    public function divisions()
    {
        return $this->hasMany(Division::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }
}
