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

    public function getTrashedNotesForMember(Member $member): Collection
    {
        return $member->notes()
            ->onlyTrashed()
            ->with('author.member')
            ->orderByDesc('deleted_at')
            ->get();
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
            'division.handle',
            'recruiter',
            'recruits',
            'recruits.division',
            'awards',
            'leave',
            'handles',
            'user',
            'trainer',
            'tags.division',
            'memberRequest',
            'platoon',
            'squad',
            'transfers.division',
            'partTimeDivisions' => fn ($q) => $q->whereActive(true),
            'activityRemindedBy',
            'activityReminders.remindedBy',
        ]);
    }

    public function getDivisionComparison(Member $member, Division $division): ?object
    {
        $tenureDays = $member->join_date ? (int) $member->join_date->diffInDays() : 0;
        $daysSinceVoice = $member->last_voice_activity
            ? (int) $member->last_voice_activity->diffInDays()
            : null;

        $stats = $division->members()
            ->whereNotNull('join_date')
            ->selectRaw('
                COUNT(*) as total_count,
                AVG(DATEDIFF(NOW(), join_date)) as avg_tenure,
                AVG(CASE WHEN last_voice_activity IS NOT NULL THEN DATEDIFF(NOW(), last_voice_activity) END) as avg_voice,
                SUM(CASE WHEN DATEDIFF(NOW(), join_date) <= ? THEN 1 ELSE 0 END) as tenure_rank,
                SUM(CASE WHEN last_voice_activity IS NOT NULL AND DATEDIFF(NOW(), last_voice_activity) >= ? THEN 1 ELSE 0 END) as activity_rank,
                SUM(CASE WHEN last_voice_activity IS NOT NULL THEN 1 ELSE 0 END) as voice_count
            ', [$tenureDays, $daysSinceVoice ?? 999999])
            ->first();

        if (! $stats || $stats->total_count === 0) {
            return null;
        }

        $avgTenureDays = (float) ($stats->avg_tenure ?? 0);
        $avgVoiceDays = (float) ($stats->avg_voice ?? 0);

        $tenurePercentile = ($stats->tenure_rank / $stats->total_count) * 100;
        $activityPercentile = $daysSinceVoice !== null && $stats->voice_count > 0
            ? ($stats->activity_rank / $stats->voice_count) * 100
            : 0;

        return (object) [
            'avgTenureDays' => round($avgTenureDays),
            'avgTenureYears' => round($avgTenureDays / 365, 1),
            'avgVoiceDays' => round($avgVoiceDays),
            'tenurePercentile' => round($tenurePercentile),
            'activityPercentile' => round($activityPercentile),
            'tenureBetter' => $tenureDays > $avgTenureDays,
            'activityBetter' => $daysSinceVoice !== null && $daysSinceVoice < $avgVoiceDays,
        ];
    }
}
