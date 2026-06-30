<?php

namespace App\Models;

use App\Enums\Rank;
use Flashadvocate\FilamentReactions\Concerns\HasReactions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Kirschbaum\Commentions\Contracts\Commentable;
use Kirschbaum\Commentions\HasComments;

class RankAction extends Model implements Commentable
{
    use HasComments;
    use HasFactory;
    use HasReactions;

    protected $casts = [
        'rank'        => Rank::class,
        'approved_at' => 'datetime',
        'accepted_at' => 'datetime',
        'awarded_at'  => 'datetime',
        'declined_at' => 'datetime',
    ];

    public $guarded = [];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function scopeApprovedAndAccepted(Builder $query): void
    {
        $query->whereNotNull('approved_at')->whereNotNull('accepted_at');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where(function ($query) {
            $query->whereNull('approved_at')
                ->whereNull('denied_at')
                ->orWhere(function ($query) {
                    $query->whereNotNull('approved_at')
                        ->whereNull('accepted_at')
                        ->whereNull('declined_at');
                });
        });
    }

    public function scopeForUser(Builder $query, $user): Builder
    {
        $member          = $user->member;
        $userRank        = $member->rank->value;
        $currentMemberId = $member->id;

        $query->whereHas('member', function (Builder $memberQuery) use ($user, $member) {
            $memberQuery
                ->when($user->isPlatoonLeader(), fn ($q) => $q->where('platoon_id', $member->platoon_id))
                ->when($user->isSquadLeader(), fn ($q) => $q->where('squad_id', $member->squad_id))
                ->when(! $user->isRole('admin'), fn ($q) => $q->where('division_id', $member->division_id));
        });

        $query->where(function ($q) use ($userRank, $currentMemberId) {
            $q->where(function ($q1) use ($userRank, $currentMemberId) {
                $q1->where('rank', '<', $userRank)
                    ->where('member_id', '<>', $currentMemberId);
            })
                ->orWhere('requester_id', $currentMemberId);
        });

        return $query;
    }

    public function isApproved(): bool
    {
        return $this->approved_at !== null;
    }

    public function actionable(): bool
    {
        return is_null($this->approved_at) && is_null($this->denied_at);
    }

    public function resolvedByRecipient(): bool
    {
        return collect([
            $this->accepted_at,
            $this->declined_at,
        ])->filter()->isNotEmpty();
    }

    public function approve(): static
    {
        $this->update([
            'approver_id' => auth()->user()->member_id,
            'approved_at' => now(),
        ]);

        return $this;
    }

    public function award(): static
    {
        $this->update(['awarded_at' => now()]);

        return $this;
    }

    public function approveAndAccept(): static
    {
        $now = now();

        $this->update([
            'approved_at' => $now,
            'accepted_at' => $now,
            'awarded_at'  => $now,
        ]);

        return $this;
    }

    public function accept(): static
    {
        $this->update(['accepted_at' => now()]);

        return $this;
    }

    public function decline(): static
    {
        $this->update(['declined_at' => now()]);

        return $this;
    }

    public function deny(string $denyReason): static
    {
        $this->update([
            'deny_reason' => $denyReason,
            'denied_at'   => now(),
        ]);

        return $this;
    }
}
