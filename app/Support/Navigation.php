<?php

namespace App\Support;

use App\Models\Member;
use App\Models\MemberRequest;
use App\Models\User;

class Navigation
{
    public static function for(User $user): array
    {
        $member = $user->member;

        return self::normalize(array_filter([
            ['label' => 'Dashboard', 'route' => 'home', 'match' => ['home', '/'], 'icon' => 'layout-dashboard'],
            ['label' => 'AOD Forums', 'href' => 'https://clanaod.net/forums', 'external' => true, 'icon' => 'message-square'],

            $user->can('recruit', Member::class)
                ? ['label' => 'Add New Recruit', 'route' => 'recruiting.initial', 'match' => ['recruit', 'recruit/*'], 'icon' => 'user-plus']
                : null,

            $user->can('manage', MemberRequest::class)
                ? ['label' => 'Member Requests', 'href' => route('filament.mod.resources.member-requests.index') . '?filters[status][value]=pending', 'external' => true, 'icon' => 'inbox']
                : null,

            $user->isRole(['admin', 'sr_ldr', 'officer'])
                ? ['label' => 'Get Help', 'route' => 'help.tickets.widget', 'match' => ['help/tickets', 'help/tickets/*'], 'icon' => 'life-buoy']
                : null,

            [
                'label'    => 'Clan Information',
                'icon'     => 'shield',
                'match'    => ['clan/*'],
                'children' => array_filter([
                    ['label' => 'Achievements', 'route' => 'awards.index', 'match' => ['clan/awards', 'clan/awards/*']],
                    ['label' => 'Clan Census Data', 'route' => 'reports.clan-census', 'match' => ['clan/census']],
                    $user->isRole('admin')
                        ? ['label' => 'Division Turnover', 'route' => 'reports.division-turnover', 'match' => ['clan/division-turnover']]
                        : null,
                    ['label' => 'Leadership Structure', 'route' => 'leadership', 'match' => ['clan/leadership']],
                    ['label' => 'Ranking Structure', 'route' => 'clan.ranks', 'match' => ['clan/ranks']],
                    ['label' => 'Code of Conduct', 'route' => 'clan.code-of-conduct', 'match' => ['clan/code-of-conduct']],
                    ['label' => 'Outstanding Inactives', 'route' => 'reports.outstanding-inactives', 'match' => ['clan/outstanding-inactives']],
                ]),
            ],

            $user->isRole(['admin', 'sr_ldr', 'officer']) || $user->isDeveloper()
                ? ['type' => 'heading', 'label' => 'Admin']
                : null,
            $user->isDeveloper()
                ? ['label' => 'Log Viewer', 'href' => url('/log-viewer'), 'external' => true, 'icon' => 'scroll-text']
                : null,
            $user->isRole('admin') || $user->isDeveloper()
                ? ['label' => 'Admin Panel', 'href' => url('/admin'), 'external' => true, 'icon' => 'sliders-horizontal']
                : null,
            $user->isRole(['sr_ldr', 'admin', 'officer']) || $user->isDeveloper()
                ? ['label' => 'Operations', 'href' => url('/operations'), 'external' => true, 'icon' => 'radio']
                : null,

            ['type' => 'heading', 'label' => 'Application'],
            [
                'label'    => 'Documentation',
                'icon'     => 'book-open',
                'match'    => ['help/docs', 'help/docs/*'],
                'children' => array_filter([
                    ['label' => 'General', 'route' => 'help', 'match' => ['help/docs']],
                    ['label' => 'Awards Images', 'route' => 'help.member-awards', 'match' => ['help/docs/member-awards']],
                    ['label' => 'Managing Rank', 'route' => 'help.managing-rank', 'match' => ['help/docs/managing-rank']],
                    $user->isRole(['admin', 'sr_ldr', 'officer'])
                        ? ['label' => 'Recruiting', 'route' => 'help.recruiting', 'match' => ['help/docs/recruiting']]
                        : null,
                    $user->isRole('admin')
                        ? ['label' => 'Contributing', 'route' => 'help.admin.home', 'match' => ['help/docs/admin']]
                        : null,
                ]),
            ],
            $user->can('train', $user)
                ? ['label' => 'Training', 'route' => 'training.index', 'match' => ['training', 'training/*'], 'icon' => 'graduation-cap']
                : null,

            $member ? ['type' => 'heading', 'label' => 'Account'] : null,
            $member ? ['label' => 'Tracker Profile', 'href' => route('member', $member->getUrlParams()), 'external' => true, 'icon' => 'user'] : null,
            $member ? ['label' => 'Forum Profile', 'href' => $member->AODProfileLink, 'external' => true, 'icon' => 'external-link'] : null,
        ]));
    }

    private static function normalize(array $items): array
    {
        return array_values(array_map(function (array $item) {
            if (isset($item['route'])) {
                $item['href'] = route($item['route']);
                unset($item['route']);
            }

            if (isset($item['children'])) {
                $item['children'] = self::normalize($item['children']);
            }

            return $item;
        }, $items));
    }
}
