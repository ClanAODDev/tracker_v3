<?php

namespace App\Models;

use App\Enums\RecommendationDecision;
use App\Enums\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Recommendation extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $with = [
        'member',
        'admin'
    ];

    protected $casts = [
        'decision' => RecommendationDecision::class,
        'type' => RecommendationDecision::class,
        'effective_at' => 'datetime',
    ];

    public function division()
    {
        return $this->hasOneThrough(Member::class, Division::class);
    }

    public function recommendable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeForRank($query): Builder
    {
        return $query->whereRecommendableType('App\Models\Rank');
    }

    public static function scopePending($query): Builder
    {
        return $query->whereDecision(RecommendationDecision::PENDING);
    }

    public static function scopeForDivision($query, $division_id): Builder
    {
        return $query->whereDivisionId($division_id);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function isPromotion(): bool
    {
        if ($this->recommendable_type === 'App\Models\Rank' && $this->member) {
            return $this->member->rank_id < $this->recommendable_id;
        }
    }

    public function scopeForCurrentUser($query): Builder
    {
        // officer -> where user squad = recommendation squad
        if (auth()->user()->role === Role::OFFICER && auth()->user()->squad) {
            $query = $query->whereHas('member.squad', function ($query) {
                $query->whereSquadId(auth()->user()->squad->id);
            });
        }

        // jr_leader -> where user platoon = recommendation platoon
        if (auth()->user()->role === Role::JUNIOR_LEADER && auth()->user()->platoon) {
            $query = $query->whereHas('member.platoon', function ($query) {
                $query->wherePlatoonId(auth()->user()->platoon->id);
            });
        }

        // sr_leader -> where user division = recommendation division
        if (auth()->user()->role === Role::SENIOR_LEADER && auth()->user()->division) {
            $query = $query->whereHas('member.division', function ($query) {
                // division id
                $query->whereDivisionId(auth()->user()->division_id);
            });
        }

        return $query;
    }

    public function squad(): HasOneThrough
    {
        return $this->hasOneThrough(Squad::class, Member::class, 'id', 'id', 'member_id', 'squad_id');
    }

    public function platoon(): HasOneThrough
    {
        return $this->hasOneThrough(Platoon::class, 'member', 'id', 'id', 'member_id', 'platoon_id');
    }
}
