<?php

namespace App\Models;

use App\Enums\Position;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Parallax\FilamentComments\Models\Traits\HasFilamentComments;

use function now;

class Transfer extends Model
{
    use HasFactory;
    use HasFilamentComments;

    public $guarded = [];

    /**
     * @return BelongsTo
     */
    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    /**
     * @return BelongsTo
     */
    public function division()
    {
        return $this->belongsTo(Division::class);
    }

    public function canApprove(): bool
    {
        return auth()->user()->can('approve', $this);
    }

    public function scopePending($query)
    {
        return $query->whereNull('approved_at');
    }

    public function approve(): void
    {
        $this->update(['approved_at' => now()]);

        $this->removeFromLeadershipAssignments();
        $this->resetTransferringMemberDetails();
    }

    protected function removeFromLeadershipAssignments(): void
    {
        $this->removeLeaderIfNeeded($this->member->position, $this->member->squad, Position::SQUAD_LEADER);
        $this->removeLeaderIfNeeded($this->member->position, $this->member->platoon, Position::PLATOON_LEADER);
    }

    protected function removeLeaderIfNeeded($position, $unit, $leaderPosition): void
    {
        if ($position->value === $leaderPosition->value && $unit && $unit->leader_id === $this->member->clan_id) {
            $unit->update(['leader_id' => 0]);
        }
    }

    protected function resetTransferringMemberDetails(): void
    {
        $this->member->update([
            'division_id' => $this->division_id,
            'position' => Position::MEMBER,
            'platoon_id' => 0,
            'squad_id' => 0,
        ]);
    }
}
