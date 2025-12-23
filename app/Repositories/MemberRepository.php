<?php

namespace App\Repositories;

use App\Models\Division;
use App\Models\Member;
use Illuminate\Support\Collection;

class MemberRepository
{
    public function search(string $name): Collection
    {
        $byName = Member::where('name', 'LIKE', "%{$name}%")->with('division');

        return Member::withWhereHas('handles', fn ($query) => $query->where('value', 'LIKE', "%{$name}%"))
            ->with('division')
            ->union($byName)
            ->orderBy('name')
            ->get();
    }

    public function searchAutocomplete(string $query, int $limit = 5): Collection
    {
        return Member::where('name', 'LIKE', "%{$query}%")
            ->take($limit)
            ->get()
            ->map(fn ($member) => [
                'id' => $member->clan_id,
                'label' => $member->name,
            ]);
    }

    public function getNotesForMember(Member $member, bool $canViewSrLdr = false): Collection
    {
        return $member->notes()
            ->with('author.member')
            ->get()
            ->filter(fn ($note) => $note->type !== 'sr_ldr' || $canViewSrLdr);
    }

    public function getRankHistory(Member $member): Collection
    {
        return $member->rankActions()->approvedAndAccepted()->get();
    }

    public function getTransfers(Member $member): Collection
    {
        return $member->transfers()->with('division')->get();
    }

    public function getPartTimeDivisions(Member $member): Collection
    {
        return $member->partTimeDivisions()->whereActive(true)->get();
    }

    public function loadProfileRelations(Member $member): Member
    {
        return $member->load([
            'division',
            'recruiter',
            'recruits',
            'recruits.division',
            'awards.award',
            'leave',
            'handles',
            'user',
            'trainer',
            'tags',
        ]);
    }

    public function getDivisionComparison(Member $member, Division $division): ?object
    {
        $divisionMembers = $division->members()->whereNotNull('join_date')->get();
        $divisionCount = $divisionMembers->count();

        if ($divisionCount === 0) {
            return null;
        }

        $tenureDays = $member->join_date ? (int) $member->join_date->diffInDays() : 0;
        $daysSinceVoice = $member->last_voice_activity
            ? (int) $member->last_voice_activity->diffInDays()
            : null;

        $avgTenureDays = $divisionMembers->avg(fn ($m) => $m->join_date->diffInDays());
        $avgVoiceDays = $divisionMembers
            ->filter(fn ($m) => $m->last_voice_activity !== null)
            ->avg(fn ($m) => $m->last_voice_activity->diffInDays()) ?? 0;

        $memberTenurePercentile = $divisionMembers
            ->filter(fn ($m) => $m->join_date->diffInDays() <= $tenureDays)
            ->count() / $divisionCount * 100;

        $memberActivityPercentile = $daysSinceVoice !== null
            ? $divisionMembers
                ->filter(fn ($m) => $m->last_voice_activity !== null && $m->last_voice_activity->diffInDays() >= $daysSinceVoice)
                ->count() / $divisionCount * 100
            : 0;

        return (object) [
            'avgTenureDays' => round($avgTenureDays),
            'avgTenureYears' => round($avgTenureDays / 365, 1),
            'avgVoiceDays' => round($avgVoiceDays),
            'tenurePercentile' => round($memberTenurePercentile),
            'activityPercentile' => round($memberActivityPercentile),
            'tenureBetter' => $tenureDays > $avgTenureDays,
            'activityBetter' => $daysSinceVoice !== null && $daysSinceVoice < $avgVoiceDays,
        ];
    }
}
