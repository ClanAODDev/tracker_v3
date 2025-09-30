<?php

namespace App\Repositories;

use App\Models\Member;

class MemberRepository
{
    public function search($name)
    {
        return Member::where('name', 'LIKE', "%{$name}%")->get();
    }
}
