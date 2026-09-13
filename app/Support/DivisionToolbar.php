<?php

namespace App\Support;

use App\Models\Division;
use App\Models\Leave;
use App\Models\Member;
use App\Models\MemberRequest;
use App\Models\Note;
use App\Models\User;

class DivisionToolbar
{
    /**
     * The ordered tool links shown above a division's dashboard, with
     * visibility already resolved for the given user.
     *
     * `tier`: 'core' items never leave the row; 'secondary' items fold into the
     * overflow menu when the row is narrow; 'overflow' items always live in the
     * "more" menu. `reports` carries a nested `menu`.
     *
     * @return array<int, array{key: string, label: string, icon: string, href: string, tier: string, external?: bool, accent?: bool, menu?: array}>
     */
    public static function for(Division $division, User $user, bool $includeHome = false): array
    {
        $slug       = $division->slug;
        $canRecruit = $user->can('recruit', Member::class);
        $tools      = [];

        if ($includeHome) {
            $tools[] = ['key' => 'home', 'label' => 'Division Home', 'icon' => 'home', 'href' => route('division', $slug), 'tier' => 'overflow'];
        }

        $tools[] = ['key' => 'members', 'label' => 'Members', 'icon' => 'users', 'href' => route('division.members', $slug), 'tier' => 'core'];

        if ($canRecruit && ! $division->isShutdown()) {
            $tools[] = ['key' => 'recruit', 'label' => 'Add Recruit', 'icon' => 'user-plus', 'href' => route('recruiting.form', $slug), 'tier' => 'core', 'accent' => true];
        }

        if ($canRecruit && $division->settings()->get('application_required', false)) {
            $tools[] = ['key' => 'applications', 'label' => 'Applications', 'icon' => 'clipboard-list', 'href' => route('division', $slug) . '?applications', 'tier' => 'core'];
        }

        $tools[] = [
            'key'   => 'reports',
            'label' => 'Reports',
            'icon'  => 'bar-chart',
            'href'  => route('division.census', $slug),
            'tier'  => 'core',
            'menu'  => [
                ['label' => 'Census', 'href' => route('division.census', $slug)],
                ['label' => 'Retention', 'href' => route('division.retention-report', $slug)],
                ['label' => 'Promotions', 'href' => route('division.promotions', $slug)],
                ['label' => 'Voice', 'href' => route('division.voice-report', $slug)],
                ['label' => 'Transfers', 'href' => route('division.transfer-report', $slug)],
            ],
        ];

        $tools[] = ['key' => 'inactives', 'label' => 'Inactives', 'icon' => 'users-round', 'href' => route('division.inactive-members', $slug), 'tier' => 'secondary'];
        $tools[] = ['key' => 'awards', 'label' => 'Awards', 'icon' => 'award', 'href' => route('awards.index', ['division' => $slug]), 'tier' => 'secondary'];

        if ($user->can('manage', MemberRequest::class)) {
            $tools[] = ['key' => 'requests', 'label' => 'Member Requests', 'icon' => 'inbox', 'href' => route('filament.mod.resources.member-requests.index'), 'tier' => 'secondary', 'external' => true];
        }

        $tools[] = ['key' => 'structure', 'label' => 'Structure', 'icon' => 'network', 'href' => route('division.structure', $slug), 'tier' => 'overflow'];
        $tools[] = ['key' => 'part-timers', 'label' => 'Part Timers', 'icon' => 'user-cog', 'href' => route('partTimers', $slug), 'tier' => 'overflow'];

        if ($user->can('create', Leave::class)) {
            $tools[] = ['key' => 'leave', 'label' => 'LOAs', 'icon' => 'calendar', 'href' => route('filament.mod.resources.leaves.index'), 'tier' => 'overflow', 'external' => true];
        }

        if ($user->can('show', Note::class)) {
            $tools[] = ['key' => 'notes', 'label' => 'Notes', 'icon' => 'sticky-note', 'href' => route('division.notes', $slug), 'tier' => 'overflow'];
        }

        return $tools;
    }
}
