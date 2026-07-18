<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClanSnapshot extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'snapshot_date' => 'date',
    ];

    public function scopeRecent(Builder $query): Builder
    {
        return $query->orderByDesc('snapshot_date');
    }
}
