<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[WithoutTimestamps]
#[Unguarded]
class ClanSnapshot extends Model
{
    use HasFactory;

    protected $casts = [
        'snapshot_date' => 'date',
    ];

    public function scopeRecent(Builder $query): Builder
    {
        return $query->orderByDesc('snapshot_date');
    }
}
