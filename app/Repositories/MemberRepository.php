<?php

namespace App\Repositories;

use App\Data\DivisionComparisonData;
use App\Models\Division;
use App\Models\Member;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Collection;

class MemberRepository
{
    public function search(string $name, bool $partial = true, int $limit = 25): Collection
    {
        return $this->searchQuery($name, $partial)
            ->limit($limit)
            ->get()
            ->load(['division', 'handles']);
    }

    public function searchPaginated(string $name, bool $partial = true, int $perPage = 25): LengthAwarePaginator
    {
        $paginator = $this->searchQuery($name, $partial)->paginate($perPage);

        $paginator->getCollection()->load(['division', 'handles']);

        return $paginator;
    }

    private function searchQuery(string $name, bool $partial): EloquentBuilder
    {
        [$operator, $pattern] = $this->searchComparison($name, $partial);

        $byName = Member::where('name', $operator, $pattern)
            ->orWhere('discord', $operator, $pattern);

        // Eager loads on either side of a union only apply to rows Eloquent
        // fetches through that builder directly — `get()` on the outer query
        // never applies `$byName`'s loads, so both relations are loaded once,
        // unconstrained, against the fetched rows by the callers.
        return Member::whereHas('handles', fn ($query) => $query->where('value', $operator, $pattern))
            ->union($byName)
            ->orderBy('name')
            ->orderBy('id');
    }

    public function searchAutocomplete(string $query, int $limit = 5, bool $partial = true): Collection
    {
        [$operator, $pattern] = $this->searchComparison($query, $partial);

        $byName = Member::where('name', $operator, $pattern)
            ->orWhere('discord', $operator, $pattern);

        return Member::withWhereHas('handles', fn ($q) => $q->where('value', $operator, $pattern))
            ->union($byName)
            ->take($limit)
            ->get()
            ->map(fn ($member) => [
                'id'    => $member->clan_id,
                'label' => $member->discord
                    ? "{$member->name} ({$member->discord})"
                    : $member->name,
            ]);
    }

    private function searchComparison(string $term, bool $partial): array
    {
        return $partial
            ? ['LIKE', '%' . $this->escapeLike($term) . '%']
            : ['=', $term];
    }

    private function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }

    public function getNotesForMember(Member $member, ?User $user = null): Collection
    {
        return $member->notes()
            ->visibleTo($user)
            ->with('author.member')
            ->get();
    }

    public function getTrashedNotesForMember(Member $member, ?User $user = null): Collection
    {
        return $member->notes()
            ->onlyTrashed()
            ->visibleTo($user)
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
            'division.handles',
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
            'unit.parent',
            'transfers.division',
            'partTimeDivisions' => fn ($q) => $q->whereActive(true),
            'activityRemindedBy',
            'activityReminders.remindedBy',
        ]);
    }

    public function getDivisionComparison(Member $member, Division $division): ?DivisionComparisonData
    {
        $tenureDays     = $member->join_date ? (int) $member->join_date->diffInDays() : 0;
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
        $avgVoiceDays  = (float) ($stats->avg_voice ?? 0);

        $tenurePercentile   = ($stats->tenure_rank / $stats->total_count) * 100;
        $activityPercentile = $daysSinceVoice !== null && $stats->voice_count > 0
            ? ($stats->activity_rank / $stats->voice_count) * 100
            : 0;

        return new DivisionComparisonData(
            avgTenureDays: (int) round($avgTenureDays),
            avgTenureYears: round($avgTenureDays / 365, 1),
            avgVoiceDays: (int) round($avgVoiceDays),
            tenurePercentile: (int) round($tenurePercentile),
            activityPercentile: (int) round($activityPercentile),
            tenureBetter: $tenureDays > $avgTenureDays,
            activityBetter: $daysSinceVoice !== null && $daysSinceVoice < $avgVoiceDays,
        );
    }
}
