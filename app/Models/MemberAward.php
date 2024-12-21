<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberAward extends Model
{
    protected $table = 'award_member';

    protected $guarded = [];

    public function member()
    {
        return $this->belongsTo(Member::class, 'member_id', 'clan_id');
    }

    public function award()
    {
        return $this->belongsTo(Award::class, 'award_id');
    }
}
