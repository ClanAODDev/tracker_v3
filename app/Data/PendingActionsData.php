<?php

namespace App\Data;

use App\Enums\Position;
use App\Enums\Rank;
use App\Models\Division;
use App\Models\DivisionApplication;
use App\Models\Leave;
use App\Models\Member;
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
            self::pushAction(
                $actions,
                $division->memberRequests()->pending()->count(),
                key: 'member-requests',
                url: route('filament.mod.resources.member-requests.index') . '?filters[status][value]=pending',
                icon: 'fa-user-plus',
                label: 'Request',
                style: 'warning',
            );
        }

        if ($user->can('recruit', Member::class) && $division->settings()->get('application_required', false)) {
            self::pushAction(
                $actions,
                DivisionApplication::where('division_id', $division->id)->count(),
                key: 'pending-applications',
                url: route('division', $division->slug) . '?applications=1',
                icon: 'fa-clipboard-list',
                label: 'Application',
            );
        }

        if ($user->member?->isAtLeast(Rank::SERGEANT)) {
            $query = RankAction::forUser($user)
                ->pending()
                ->whereHas('member', fn ($q) => $q->where('division_id', $division->id));

            $url = route('filament.mod.resources.rank-actions.index');

            if ($user->isRole('admin')) {
                $query->where('rank', '>=', Rank::SERGEANT->value);
                $url .= '?tableFilters[sgt_plus][isActive]=true';
            }

            self::pushAction(
                $actions,
                $query->count(),
                key: 'pending-rank-actions',
                url: $url,
                icon: 'fa-arrow-up',
                label: 'Rank Action',
            );
        }

        if ($user->isRole('sr_ldr')) {
            self::pushAction(
                $actions,
                $division->members()
                    ->whereDoesntHave('leave')
                    ->where('last_voice_activity', '<', now()->subDays($maxDays)->format('Y-m-d'))
                    ->count(),
                key: 'inactive-members',
                url: route('division.inactive-members', $division),
                icon: 'fa-user-clock',
                label: 'Outstanding Inactive',
                style: 'warning',
            );
        }

        if ($user->isDivisionLeader()) {
            self::pushAction(
                $actions,
                MemberAward::needsApproval()
                    ->whereHas('award', fn ($q) => $q->where('division_id', $division->id))
                    ->count(),
                key: 'award-requests',
                url: route('filament.mod.resources.member-awards.index') . reviewDivisionAwardsQuery($division->id),
                icon: 'fa-trophy',
                label: 'Award',
            );
        }

        if ($user->isRole('admin')) {
            self::pushAction(
                $actions,
                MemberAward::needsApproval()
                    ->whereHas('award', fn ($q) => $q->whereNull('division_id'))
                    ->count(),
                key: 'clan-award-requests',
                url: route('filament.mod.resources.member-awards.index') . '?filters[needs approval][isActive]=true&filters[clan_wide][isActive]=true',
                icon: 'fa-globe',
                label: 'Clan Award',
                style: 'accent',
                adminOnly: true,
            );
        }

        if ($user->isDivisionLeader()) {
            self::pushAction(
                $actions,
                $division->transfers()->pending()->count(),
                key: 'pending-transfers',
                url: route('filament.mod.resources.transfers.index') . '?filters[incomplete][isActive]=true&filters[transferring_to][value]=' . $division->id,
                icon: 'fa-exchange-alt',
                label: 'Transfer',
            );
        }

        if ($user->isDivisionLeader() || $user->isRole(['admin', 'sr_ldr'])) {
            self::pushAction(
                $actions,
                Leave::whereNull('approver_id')
                    ->whereHas('member', fn ($q) => $q->where('division_id', $division->id))
                    ->count(),
                key: 'pending-leaves',
                url: route('filament.mod.resources.leaves.index') . '?filters[needs approval][isActive]=true',
                icon: 'fa-calendar-alt',
                label: 'LOA',
            );

            self::pushAction(
                $actions,
                Leave::whereNotNull('approver_id')
                    ->whereBetween('end_date', [now()->startOfDay(), now()->addDays(14)->endOfDay()])
                    ->whereHas('member', fn ($q) => $q->where('division_id', $division->id))
                    ->count(),
                key: 'expiring-leaves',
                url: route('filament.mod.resources.leaves.index') . '?filters[expiring soon][isActive]=true',
                icon: 'fa-calendar-check',
                label: 'LOA Expiring',
                style: 'warning',
            );

            self::pushAction(
                $actions,
                Leave::whereNotNull('approver_id')
                    ->where('end_date', '<', now()->startOfDay())
                    ->whereHas('member', fn ($q) => $q->where('division_id', $division->id))
                    ->count(),
                key: 'overdue-leaves',
                url: route('filament.mod.resources.leaves.index') . '?filters[overdue][isActive]=true',
                icon: 'fa-calendar-times',
                label: 'LOA Overdue',
                style: 'danger',
            );
        }

        if ($user->isRole('sr_ldr')) {
            self::pushAction(
                $actions,
                $division->members()->misconfiguredDiscord()->count(),
                key: 'voice-issues',
                url: route('division.voice-report', $division->slug),
                icon: 'fa-headset',
                label: 'Voice Issue',
            );
        }

        if ($user->can('create', [Platoon::class, $division])) {
            self::pushAction(
                $actions,
                count($division->unassigned),
                key: 'unassigned-members',
                url: route('division', $division->slug) . '#platoons',
                icon: 'fa-user-slash',
                label: 'No Platoon',
            );
        }

        if ($user->can('manageUnassigned', User::class)) {
            self::pushAction(
                $actions,
                $division->members()
                    ->where('platoon_id', '>', 0)
                    ->where('squad_id', 0)
                    ->where('position', Position::MEMBER)
                    ->count(),
                key: 'unassigned-to-squad',
                url: '#',
                icon: 'fa-users-slash',
                label: 'No Squad',
                modalTarget: 'no-squad-modal',
            );
        }

        if ($user->isRole('admin')) {
            self::pushAction(
                $actions,
                Ticket::whereIn('state', ['new', 'assigned'])->count(),
                key: 'open-tickets',
                url: route('help.tickets.widget') . '?view=all',
                icon: 'fa-ticket-alt',
                label: 'Open Ticket',
                style: 'warning',
                adminOnly: true,
            );
        }

        return new self($actions);
    }

    private static function pushAction(
        Collection $actions,
        int $count,
        string $key,
        string $url,
        string $icon,
        string $label,
        string $style = 'default',
        bool $adminOnly = false,
        ?string $modalTarget = null,
    ): void {
        if ($count <= 0) {
            return;
        }

        $actions->push(new PendingAction(
            key: $key,
            count: $count,
            url: $url,
            icon: $icon,
            label: $label,
            style: $style,
            adminOnly: $adminOnly,
            modalTarget: $modalTarget,
        ));
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
