<?php

namespace App\Data;

use App\Models\Division;
use App\Models\Member;
use Illuminate\Support\Collection;

class DivisionVoiceReportData
{
    private Collection $discordIssues;

    private array $stats;

    public function __construct(private Division $division)
    {
        $this->discordIssues = $division->members()
            ->misconfiguredDiscord()
            ->with('platoon')
            ->orderBy('last_voice_status')
            ->orderBy('name')
            ->get();

        $groupedByStatus = $this->discordIssues->groupBy(fn ($m) => $m->last_voice_status->value);

        $this->stats = [
            'total'           => $this->discordIssues->count(),
            'disconnected'    => $groupedByStatus->get('disconnected')?->count() ?? 0,
            'neverConnected'  => $groupedByStatus->get('never_connected')?->count() ?? 0,
            'neverConfigured' => $groupedByStatus->get('never_configured')?->count() ?? 0,
        ];
    }

    public static function for(Division $division): self
    {
        return new self($division);
    }

    public function toArray(): array
    {
        return [
            'division' => [
                'name'         => $this->division->name,
                'slug'         => $this->division->slug,
                'platoonLabel' => $this->division->locality('platoon'),
            ],
            'stats'   => $this->stats,
            'members' => $this->discordIssues->map(fn (Member $member) => [
                'rankName'    => $member->present()->rankName,
                'status'      => $member->last_voice_status->value,
                'statusLabel' => $member->last_voice_status->getLabel(),
                'platoon'     => $member->platoon?->name,
                'discord'     => $member->discord,
                'lastActive'  => $member->present()->lastActive('last_voice_activity'),
                'url'         => route('member', $member->getUrlParams()),
                'forumUrl'    => doForumFunction([$member->clan_id], 'forumProfile'),
            ])->values(),
        ];
    }
}
