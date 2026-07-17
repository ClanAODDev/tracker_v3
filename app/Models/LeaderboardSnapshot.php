<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaderboardSnapshot extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'snapshot_date' => 'date',
        'trend_data' => 'array',
    ];

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function scopeForCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    public function scopeForDivision(Builder $query, int $divisionId): Builder
    {
        return $query->where('division_id', $divisionId);
    }

    public function scopeRecent(Builder $query): Builder
    {
        return $query->orderByDesc('snapshot_date');
    }
}
