<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Parallax\FilamentComments\Models\Traits\HasFilamentComments;

class DivisionApplication extends Model
{
    use HasFilamentComments;

    protected $guarded = [];

    protected static function booted(): void
    {
        static::deleting(function (DivisionApplication $application) {
            $application->filamentComments()->forceDelete();
        });
    }

    protected $casts = [
        'responses' => 'array',
        'recruited_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->whereNull('recruited_at');
    }
}
