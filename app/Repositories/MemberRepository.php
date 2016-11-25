<?php

namespace App\Repositories;

use App\Member;

class MemberRepository
{

    public function search($name)
    {
        return Member::where('name', 'LIKE', "%{$name}%")->get();
    }

    public function staffSergeants()
    {
        return Member::where('rank_id', 10)->get();
    }
}
