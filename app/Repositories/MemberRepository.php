<?php

namespace App\Repositories;


class MemberRepository
{

    public function search($name)
    {
        return Member::where('name', 'LIKE', "%{$name}%")->get();
    }
}