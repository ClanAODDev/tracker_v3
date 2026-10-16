<?php

namespace App\Data;

use App\Models\MemberAward;
use Illuminate\Support\Collection;

final readonly class TenureReconciliation
{
    /**
     * @param  Collection<int, MemberAward>  $granted
     */
    public function __construct(
        public int $missing,
        public int $invalid,
        public Collection $granted,
    ) {}
}
