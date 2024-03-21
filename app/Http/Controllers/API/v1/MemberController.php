<?php

namespace App\Http\Controllers\API\v1;

use App\Models\Member;
use App\Transformers\MemberFullTransformer;

class MemberController extends ApiController
{
    protected MemberFullTransformer $memberTransformer;

    public function __construct(MemberFullTransformer $memberTransformer)
    {
        $this->memberTransformer = $memberTransformer;
    }

    public function show($clan_id)
    {
        $member = Member::whereClanId($clan_id)->first();

        if (! $member) {
            return $this->respondNotFound();
        }

        // TODO
    }
}
