<?php

namespace App\Models;

use App\Enums\ActivityType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Kirschbaum\Commentions\Contracts\Commentable;
use Kirschbaum\Commentions\HasComments;

class Transfer extends Model implements Commentable
{
    use HasComments;
    use HasFactory;

    public $guarded = [];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopePending(Builder $query): void
    {
        $query->whereNull('approved_at');
    }

    public function canApprove(): bool
    {
        return auth()->user()->can('approve', $this);
    }

    public function approve(): void
    {
        $this->update([
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        $this->member->recordActivity(ActivityType::TRANSFERRED, [
            'to_division' => $this->division->name,
        ]);

        $this->member->moveToDivision($this->division_id);
    }
}
