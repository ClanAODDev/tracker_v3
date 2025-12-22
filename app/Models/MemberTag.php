<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class MemberTag extends Pivot
{
    protected $table = 'member_tag';

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function divisionTag(): BelongsTo
    {
        return $this->belongsTo(DivisionTag::class);
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'assigned_by');
    }
}
