<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[WithoutTimestamps]
#[Unguarded]
class LeaderboardSnapshot extends Model
{
    use HasFactory;

    protected $casts = [
        'snapshot_date' => 'date',
        'trend_data'    => 'array',
    ];

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    #[Scope]
    protected function forCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    #[Scope]
    protected function forDivision(Builder $query, int $divisionId): Builder
    {
        return $query->where('division_id', $divisionId);
    }

    #[Scope]
    protected function recent(Builder $query): Builder
    {
        return $query->orderByDesc('snapshot_date');
    }
}
