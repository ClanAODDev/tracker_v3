<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Award extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function recipients()
    {
        return $this->belongsToMany(Member::class, 'award_member', 'award_id', 'member_id')
            ->using(MemberAward::class)
            ->withPivot(['reason', 'expires_at'])
            ->withTimestamps();
    }
}
