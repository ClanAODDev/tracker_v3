<?php

namespace App\Models;

use App\Enums\Ability;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Table('award_member')]
#[Unguarded]
class MemberAward extends Model
{
    use HasFactory;

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

    #[Scope]
    protected function visibleTo(Builder $query, User $user): void
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

    #[Scope]
    protected function needsApproval(Builder $query): void
    {
        $query->where('approved', 0);
    }
}
