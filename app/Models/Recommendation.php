<?php

namespace App\Models;

use App\Enums\RecommendationDecision;
use App\Enums\RecommendationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recommendation extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'decision' => RecommendationDecision::class,
        'type' => RecommendationDecision::class,
    ];

    public static function scopePromotions($query)
    {
        return $query->whereType(RecommendationType::PROMOTION)->orderBy('created_at');
    }

    public static function scopeDemotions($query)
    {
        return $query->whereType(RecommendationType::DEMOTION)->orderBy('created_at');
    }

    public static function scopeForCurrentMonth($query)
    {
        return $query->whereMonth('effective_at', date('m'));
    }
}
