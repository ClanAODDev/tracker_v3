<?php

namespace App\Support;

use App\Models\Member;

class MemberCard
{
    /**
     * The canonical shape for referencing a member from another page — a
     * leader card, an award recipient, a profile header. Callers spread this
     * and add context-specific fields on top.
     *
     * @return array<string, mixed>|null
     */
    public static function from(?Member $member): ?array
    {
        if (! $member) {
            return null;
        }

        return [
            'name'       => $member->name,
            'clanId'     => $member->clan_id,
            'rankName'   => $member->present()->rankName(),
            'rankAbbr'   => $member->rank->getAbbreviation(),
            'rankColor'  => $member->rank->getColorHex(),
            'position'   => $member->position?->getLabel(),
            'avatarUrl'  => $member->getDiscordAvatarUrl(),
            'profileUrl' => route('member', $member->getUrlParams()),
            'division'   => $member->division?->name,
        ];
    }

    /**
     * A minimal "rank + name linking to the profile" reference, for the many
     * spots that only need to render a member as a link (recruiter, trainer,
     * note author, recruit list).
     *
     * @return array{name: string, url: string}|null
     */
    public static function ref(?Member $member): ?array
    {
        if (! $member) {
            return null;
        }

        return [
            'name' => $member->present()->rankName(),
            'url'  => route('member', $member->getUrlParams()),
        ];
    }
}
