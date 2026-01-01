<?php

namespace App\Data;

use App\Enums\Position;
use App\Enums\Rank;
use App\Models\Division;
use App\Models\Leave;
use App\Models\MemberAward;
use App\Models\MemberRequest;
use App\Models\Platoon;
use App\Models\RankAction;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Collection;

readonly class PendingActionsData
{
    public function __construct(
        public Collection $actions,
    ) {}

    public static function forDivision(Division $division, User $user): self
    {
        $actions = collect();
        $maxDays = config('aod.maximum_days_inactive');

        if ($user->can('manage', MemberRequest::class)) {
            $count = $division->memberRequests()->pending()->count();
            if ($count > 0) {
                $actions->push(new PendingAction(
                    key: 'member-requests',
                    count: $count,
                    url: route('filament.mod.resources.member-requests.index') . '?filters[status][value]=pending',
                    icon: 'fa-user-plus',
                    label: 'Request',
                    style: 'warning',
                ));
            }
        }

        if ($user->member && $user->member->rank->value >= Rank::SERGEANT->value) {
            $query = RankAction::forUser($user)
                ->pending()
                ->whereHas('member', fn ($q) => $q->where('division_id', $division->id));

            $url = route('filament.mod.resources.rank-actions.index');

            if ($user->isRole('admin')) {
                $query->where('rank', '>=', Rank::SERGEANT->value);
                $url .= '?tableFilters[sgt_plus][isActive]=true';
            }

            $count = $query->count();
            if ($count > 0) {
                $actions->push(new PendingAction(
                    key: 'pending-rank-actions',
                    count: $count,
                    url: $url,
                    icon: 'fa-arrow-up',
                    label: 'Rank Action',
                ));
            }
        }

        if ($user->isRole('sr_ldr')) {
            $count = $division->members()
                ->whereDoesntHave('leave')
                ->where('last_voice_activity', '<', now()->subDays($maxDays)->format('Y-m-d'))
                ->count();
            if ($count > 0) {
                $actions->push(new PendingAction(
                    key: 'inactive-members',
                    count: $count,
                    url: route('division.inactive-members', $division),
                    icon: 'fa-user-clock',
                    label: 'Outstanding Inactive',
                    style: 'warning',
                ));
            }
        }

        if ($user->isDivisionLeader()) {
            $count = MemberAward::needsApproval()
                ->whereHas('award', fn ($q) => $q->where('division_id', $division->id))
                ->count();
            if ($count > 0) {
                $actions->push(new PendingAction(
                    key: 'award-requests',
                    count: $count,
                    url: route('filament.mod.resources.member-awards.index') . reviewDivisionAwardsQuery($division->id),
                    icon: 'fa-trophy',
                    label: 'Award',
                ));
            }
        }

        if ($user->isRole('admin')) {
            $count = MemberAward::needsApproval()
                ->whereHas('award', fn ($q) => $q->whereNull('division_id'))
                ->count();
            if ($count > 0) {
                $actions->push(new PendingAction(
                    key: 'clan-award-requests',
                    count: $count,
                    url: route('filament.mod.resources.member-awards.index') . '?filters[needs approval][isActive]=true&filters[clan_wide][isActive]=true',
                    icon: 'fa-globe',
                    label: 'Clan Award',
                    style: 'accent',
                    adminOnly: true,
                ));
            }
        }

        if ($user->isDivisionLeader()) {
            $count = $division->transfers()->pending()->count();
            if ($count > 0) {
                $actions->push(new PendingAction(
                    key: 'pending-transfers',
                    count: $count,
                    url: route('filament.mod.resources.transfers.index') . '?filters[incomplete][isActive]=true&filters[transferring_to][value]=' . $division->id,
                    icon: 'fa-exchange-alt',
                    label: 'Transfer',
                ));
            }
        }

        if ($user->isRole(['admin', 'sr_ldr'])) {
            $count = Leave::whereNull('approver_id')
                ->whereHas('member', fn ($q) => $q->where('division_id', $division->id))
                ->count();
            if ($count > 0) {
                $actions->push(new PendingAction(
                    key: 'pending-leaves',
                    count: $count,
                    url: route('filament.mod.resources.leaves.index') . '?filters[pending][isActive]=true',
                    icon: 'fa-calendar-alt',
                    label: 'LOA',
                ));
            }
        }

        if ($user->isRole('sr_ldr')) {
            $count = $division->members()->misconfiguredDiscord()->count();
            if ($count > 0) {
                $actions->push(new PendingAction(
                    key: 'voice-issues',
                    count: $count,
                    url: route('division.voice-report', $division->slug),
                    icon: 'fa-headset',
                    label: 'Voice Issue',
                ));
            }
        }

        if ($user->can('create', [Platoon::class, $division])) {
            $count = count($division->unassigned);
            if ($count > 0) {
                $actions->push(new PendingAction(
                    key: 'unassigned-members',
                    count: $count,
                    url: route('division', $division->slug) . '#platoons',
                    icon: 'fa-user-slash',
                    label: 'No Platoon',
                ));
            }
        }

        if ($user->isRole('sr_ldr')) {
            $count = $division->members()
                ->where('platoon_id', '>', 0)
                ->where('squad_id', 0)
                ->where('position', Position::MEMBER)
                ->count();
            if ($count > 0) {
                $actions->push(new PendingAction(
                    key: 'unassigned-to-squad',
                    count: $count,
                    url: '#',
                    icon: 'fa-users-slash',
                    label: 'No Squad',
                    modalTarget: 'no-squad-modal',
                ));
            }
        }

        if ($user->isRole('admin')) {
            $count = Ticket::whereIn('state', ['new', 'assigned'])->count();
            if ($count > 0) {
                $actions->push(new PendingAction(
                    key: 'open-tickets',
                    count: $count,
                    url: route('filament.admin.resources.tickets.index') . '?' . http_build_query([
                        'filters' => [
                            'state' => ['values' => ['new', 'assigned']],
                        ],
                    ]),
                    icon: 'fa-ticket-alt',
                    label: 'Open Ticket',
                    style: 'warning',
                    adminOnly: true,
                ));
            }
        }

        return new self($actions);
    }

    public function hasAnyActions(): bool
    {
        return $this->actions->isNotEmpty();
    }

    public function total(): int
    {
        return $this->actions->sum('count');
    }

    public function get(string $key): ?PendingAction
    {
        return $this->actions->firstWhere('key', $key);
    }

    public function has(string $key): bool
    {
        return $this->get($key) !== null;
    }

    public function divisionActions(): Collection
    {
        return $this->actions->filter(fn (PendingAction $action) => ! $action->adminOnly);
    }
}
