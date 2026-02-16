<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Kirschbaum\Commentions\Contracts\Commentable;
use Kirschbaum\Commentions\HasComments;

class DivisionApplication extends Model implements Commentable
{
    use HasComments;

    protected $guarded = [];

    protected static function booted(): void
    {
        static::deleting(function (DivisionApplication $application) {
            $application->comments()->delete();
        });
    }

    protected $casts = [
        'responses'    => 'array',
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
