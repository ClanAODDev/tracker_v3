<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MemberAward extends Model
{
    use HasFactory;

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

    public function award()
    {
        return $this->belongsTo(Award::class)->orderBy('display_order');
    }

    public function scopeNeedsApproval(Builder $query): void
    {
        $query->where('approved', 0);
    }
}
