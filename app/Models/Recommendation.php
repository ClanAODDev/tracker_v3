<?php

namespace App\Models;

use App\Enums\RecommendationDecision;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Recommendation extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'decision' => RecommendationDecision::class,
        'type' => RecommendationDecision::class,
    ];

    public function recommendable(): MorphTo
    {
        return $this->morphTo();
    }

    public static function scopeForCurrentMonth($query)
    {
        return $query->whereMonth('effective_at', date('m'));
    }

    public function member():HasOne
    {
        return $this->hasOne(Member::class);
    }

    public function admin(): HasOne
    {
        return $this->hasOne(Member::class);
    }

    public function isPromotion(): bool
    {
        if ($this->recommendable_type === 'App\Models\Rank' && $this->member) {
            return $this->member->rank_id < $this->recommendable_id;
        }
    }
}
