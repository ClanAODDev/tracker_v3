<?php

namespace App\Models;

use App\Enums\Ability;
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

    public function scopeVisibleTo(Builder $query, User $user): void
    {
        $isAdmin  = $user->isRole('admin');
        $isLeader = $user->isDivisionLeader();

        if (! $isAdmin && ! $isLeader) {
            return;
        }

        $divisionId = $isLeader ? $user->member?->division_id : null;
        $clanWide   = $isAdmin || $user->can(Ability::ManageMemberAwards);

        $query->whereHas('award', fn (Builder $award) => $award->where(fn (Builder $scope) => $scope
            ->when($divisionId, fn (Builder $q) => $q->where('division_id', $divisionId))
            ->when($clanWide, fn (Builder $q) => $q->orWhereNull('division_id'))));
    }

    public function scopeNeedsApproval(Builder $query): void
    {
        $query->where('approved', 0);
    }
}
