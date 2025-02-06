<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MemberAward extends Model
{
    protected $table = 'award_member';

    protected $guarded = [];

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id', 'clan_id');
    }

    public function requester()
    {
        return $this->belongsTo(Member::class);
    }

    public function division()
    {
        return $this->hasManyThrough(Division::class, Award::class, 'id', 'id', 'award_id', 'division_id');
    }

    public function award()
    {
        return $this->belongsTo(Award::class)->orderBy('display_order');
    }

    public function scopeNeedsApproval(Builder $query): void
    {
        $query->where('approved', 0);
    }
}
