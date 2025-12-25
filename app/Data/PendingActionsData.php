<?php

namespace App\Data;

use App\Enums\Position;
use App\Models\Division;
use App\Models\Leave;
use App\Models\MemberAward;
use App\Models\MemberRequest;
use App\Models\Platoon;
use App\Models\User;

readonly class PendingActionsData
{
    public function __construct(
        public ?int $memberRequests,
        public ?int $inactiveMembers,
        public ?int $awardRequests,
        public ?int $clanAwardRequests,
        public ?int $pendingTransfers,
        public ?int $pendingLeaves,
        public ?int $voiceIssues,
        public ?int $unassignedMembers,
        public ?int $unassignedToSquad,
        public ?string $memberRequestsUrl,
        public ?string $inactiveMembersUrl,
        public ?string $awardRequestsUrl,
        public ?string $clanAwardRequestsUrl,
        public ?string $pendingTransfersUrl,
        public ?string $pendingLeavesUrl,
        public ?string $voiceIssuesUrl,
        public ?string $unassignedMembersUrl,
        public ?string $unassignedToSquadUrl,
    ) {}

    public static function forDivision(Division $division, User $user): self
    {
        $maxDays = config('aod.maximum_days_inactive');

        $memberRequests = null;
        $memberRequestsUrl = null;
        if ($user->can('manage', MemberRequest::class)) {
            $memberRequests = $division->memberRequests()->pending()->count();
            if ($memberRequests > 0) {
                $memberRequestsUrl = route('filament.mod.resources.member-requests.index') . '?tableFilters[status][value]=pending';
            }
        }

        $inactiveMembers = null;
        $inactiveMembersUrl = null;
        if ($user->isRole('sr_ldr')) {
            $inactiveMembers = $division->members()
                ->whereDoesntHave('leave')
                ->where('last_voice_activity', '<', now()->subDays($maxDays)->format('Y-m-d'))
                ->count();
            if ($inactiveMembers > 0) {
                $inactiveMembersUrl = route('division.inactive-members', $division);
            }
        }

        $awardRequests = null;
        $awardRequestsUrl = null;
        if ($user->isDivisionLeader()) {
            $awardRequests = MemberAward::needsApproval()
                ->whereHas('award', fn ($q) => $q->where('division_id', $division->id))
                ->count();
            if ($awardRequests > 0) {
                $awardRequestsUrl = route('filament.mod.resources.member-awards.index') . reviewDivisionAwardsQuery($division->id);
            }
        }

        $clanAwardRequests = null;
        $clanAwardRequestsUrl = null;
        if ($user->isRole('admin')) {
            $clanAwardRequests = MemberAward::needsApproval()
                ->whereHas('award', fn ($q) => $q->whereNull('division_id'))
                ->count();
            if ($clanAwardRequests > 0) {
                $clanAwardRequestsUrl = route('filament.admin.resources.member-awards.index') . '?tableFilters[pending][isActive]=true&tableFilters[clan_wide][isActive]=true';
            }
        }

        $pendingTransfers = null;
        $pendingTransfersUrl = null;
        if ($user->isDivisionLeader()) {
            $pendingTransfers = $division->transfers()->pending()->count();
            if ($pendingTransfers > 0) {
                $pendingTransfersUrl = route('filament.mod.resources.transfers.index') . '?tableFilters[incomplete][isActive]=true&tableFilters[transferring_to][value]=' . $division->id;
            }
        }

        $pendingLeaves = null;
        $pendingLeavesUrl = null;
        if ($user->isRole(['admin', 'sr_ldr'])) {
            $pendingLeaves = Leave::whereNull('approver_id')
                ->whereHas('member', fn ($q) => $q->where('division_id', $division->id))
                ->count();
            if ($pendingLeaves > 0) {
                $pendingLeavesUrl = route('filament.mod.resources.leaves.index') . '?tableFilters[pending][isActive]=true';
            }
        }

        $voiceIssues = null;
        $voiceIssuesUrl = null;
        if ($user->isRole(['sr_ldr', 'jr_ldr'])) {
            $voiceIssues = $division->members()->misconfiguredDiscord()->count();
            if ($voiceIssues > 0) {
                $voiceIssuesUrl = route('division.voice-report', $division->slug);
            }
        }

        $unassignedMembers = null;
        $unassignedMembersUrl = null;
        if ($user->can('create', [Platoon::class, $division])) {
            $unassignedMembers = count($division->unassigned);
            if ($unassignedMembers > 0) {
                $unassignedMembersUrl = route('division', $division->slug) . '#platoons';
            }
        }

        $unassignedToSquad = null;
        $unassignedToSquadUrl = null;
        if ($user->isRole(['sr_ldr', 'jr_ldr'])) {
            $unassignedToSquad = $division->members()
                ->where('platoon_id', '>', 0)
                ->where('squad_id', 0)
                ->where('position', Position::MEMBER)
                ->count();
            if ($unassignedToSquad > 0) {
                $unassignedToSquadUrl = route('division', $division->slug) . '#platoons';
            }
        }

        return new self(
            memberRequests: $memberRequests,
            inactiveMembers: $inactiveMembers,
            awardRequests: $awardRequests,
            clanAwardRequests: $clanAwardRequests,
            pendingTransfers: $pendingTransfers,
            pendingLeaves: $pendingLeaves,
            voiceIssues: $voiceIssues,
            unassignedMembers: $unassignedMembers,
            unassignedToSquad: $unassignedToSquad,
            memberRequestsUrl: $memberRequestsUrl,
            inactiveMembersUrl: $inactiveMembersUrl,
            awardRequestsUrl: $awardRequestsUrl,
            clanAwardRequestsUrl: $clanAwardRequestsUrl,
            pendingTransfersUrl: $pendingTransfersUrl,
            pendingLeavesUrl: $pendingLeavesUrl,
            voiceIssuesUrl: $voiceIssuesUrl,
            unassignedMembersUrl: $unassignedMembersUrl,
            unassignedToSquadUrl: $unassignedToSquadUrl,
        );
    }

    public function hasAnyActions(): bool
    {
        return ($this->memberRequests ?? 0) > 0
            || ($this->inactiveMembers ?? 0) > 0
            || ($this->awardRequests ?? 0) > 0
            || ($this->clanAwardRequests ?? 0) > 0
            || ($this->pendingTransfers ?? 0) > 0
            || ($this->pendingLeaves ?? 0) > 0
            || ($this->voiceIssues ?? 0) > 0
            || ($this->unassignedMembers ?? 0) > 0
            || ($this->unassignedToSquad ?? 0) > 0;
    }

    public function total(): int
    {
        return ($this->memberRequests ?? 0)
            + ($this->inactiveMembers ?? 0)
            + ($this->awardRequests ?? 0)
            + ($this->clanAwardRequests ?? 0)
            + ($this->pendingTransfers ?? 0)
            + ($this->pendingLeaves ?? 0)
            + ($this->voiceIssues ?? 0)
            + ($this->unassignedMembers ?? 0)
            + ($this->unassignedToSquad ?? 0);
    }
}
