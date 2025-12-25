<?php

namespace App\Data;

use App\Enums\Position;
use App\Models\Division;
use App\Models\MemberRequest;
use App\Models\Platoon;
use App\Models\User;

readonly class PendingActionsData
{
    public function __construct(
        public ?int $memberRequests,
        public ?int $inactiveMembers,
        public ?int $awardRequests,
        public ?int $pendingTransfers,
        public ?int $voiceIssues,
        public ?int $unassignedMembers,
        public ?int $unassignedToSquad,
        public ?string $memberRequestsUrl,
        public ?string $inactiveMembersUrl,
        public ?string $awardRequestsUrl,
        public ?string $pendingTransfersUrl,
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
            $awardRequests = $division->awards()->whereHas('unapprovedRecipients')->count();
            if ($awardRequests > 0) {
                $awardRequestsUrl = route('filament.mod.resources.member-awards.index') . reviewDivisionAwardsQuery($division->id);
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
            pendingTransfers: $pendingTransfers,
            voiceIssues: $voiceIssues,
            unassignedMembers: $unassignedMembers,
            unassignedToSquad: $unassignedToSquad,
            memberRequestsUrl: $memberRequestsUrl,
            inactiveMembersUrl: $inactiveMembersUrl,
            awardRequestsUrl: $awardRequestsUrl,
            pendingTransfersUrl: $pendingTransfersUrl,
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
            || ($this->pendingTransfers ?? 0) > 0
            || ($this->voiceIssues ?? 0) > 0
            || ($this->unassignedMembers ?? 0) > 0
            || ($this->unassignedToSquad ?? 0) > 0;
    }

    public function total(): int
    {
        return ($this->memberRequests ?? 0)
            + ($this->inactiveMembers ?? 0)
            + ($this->awardRequests ?? 0)
            + ($this->pendingTransfers ?? 0)
            + ($this->voiceIssues ?? 0)
            + ($this->unassignedMembers ?? 0)
            + ($this->unassignedToSquad ?? 0);
    }
}
