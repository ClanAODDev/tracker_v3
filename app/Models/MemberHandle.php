<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

#[Table('handle_member', incrementing: true)]
#[Unguarded]
class MemberHandle extends Pivot
{
    use HasFactory;

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function handle(): BelongsTo
    {
        return $this->belongsTo(Handle::class);
    }
}
