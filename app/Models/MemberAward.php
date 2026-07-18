<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberAward extends Model
{
    use HasFactory;

    protected $table = 'award_member';

    protected $guarded = [];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'member_id');
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function award(): BelongsTo
    {
        return $this->belongsTo(Award::class);
    }

    public function scopeNeedsApproval(Builder $query): void
    {
        $query->where('approved', 0);
    }
}
