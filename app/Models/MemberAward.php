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
        if ($user->isRole('admin')) {
            $query->whereHas('award', fn (Builder $award) => $award->whereNull('division_id'));

            return;
        }

        if ($user->isDivisionLeader()) {
            $query->whereHas('award', fn (Builder $award) => $award->where(function (Builder $scope) use ($user) {
                $scope->where('division_id', $user->member->division_id);

                if ($user->can(Ability::ManageMemberAwards)) {
                    $scope->orWhereNull('division_id');
                }
            }));
        }
    }

    public function scopeNeedsApproval(Builder $query): void
    {
        $query->where('approved', 0);
    }
}
