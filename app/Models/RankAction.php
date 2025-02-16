<?php

namespace App\Models;

use App\Enums\Rank;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Parallax\FilamentComments\Models\Traits\HasFilamentComments;

class RankAction extends Model
{
    use HasFactory;
    use HasFilamentComments;

    protected $casts = [
        'rank' => Rank::class,
        'approved_at' => 'datetime',
        'accepted_at' => 'datetime',
        'declined_at' => 'datetime',
    ];

    public $guarded = [];

    /**
     * @return BelongsTo
     */
    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    public function requester()
    {
        return $this->belongsTo(Member::class);
    }

    public function scopeApprovedAndAccepted(Builder $query): void
    {
        $query->whereNot('approved_at', null)
            ->whereNot('accepted_at', null);
    }

    public function isApproved()
    {
        return $this->approved_at;
    }

    public function approve()
    {
        $this->update([
            'approved_at' => now(),
        ]);

        return $this;
    }

    public function approveAndAccept()
    {
        $this->update([
            'approved_at' => now(),
            'accepted_at' => now(),
        ]);

        return $this;
    }

    public function accept()
    {
        $this->update([
            'accepted_at' => now(),
        ]);

        return $this;
    }

    public function decline()
    {
        $this->update([
            'declined_at' => now(),
        ]);

        return $this;
    }

    public function deny($deny_reason)
    {
        $this->update([
            'deny_reason' => $deny_reason,
            'denied_at' => now(),
        ]);

        return $this;
    }

    public function actionable()
    {
        return is_null($this->approved_at) && is_null($this->denied_at);
    }

    public function resolved()
    {
        return collect([
            $this->accepted_at,
            $this->declined_at,
        ])->filter()->isNotEmpty();
    }
}
