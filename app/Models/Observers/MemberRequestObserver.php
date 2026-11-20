<?php

namespace App\Models\Observers;

use App\Models\MemberRequest;
use App\Services\RecruitmentService;

class MemberRequestObserver
{
    public function deleted(MemberRequest $request): void
    {
        app(RecruitmentService::class)->withdrawRecruitment($request);
    }
}
