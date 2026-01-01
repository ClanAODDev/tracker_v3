<?php

namespace App\Services;

use App\AOD\MemberSync\GetDivisionInfo;
use App\Enums\Role;
use App\Models\Division;
use App\Models\Member;
use App\Models\MemberRequest;
use App\Models\Platoon;
use App\Models\Squad;
use App\Notifications\Channel\NotifyDivisionMemberRemoved;
use Closure;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class MemberSyncService
{
    protected const FORUM_RANK_OFFSET = 2;

    protected const SYNCABLE_FIELDS = [
        'allow_pm',
        'discord',
        'discord_id',
        'division_id',
        'name',
        'posts',
        'privacy_flag',
        'ts_unique_id',
        'last_voice_status',
        'rank',
        'last_activity',
        'last_voice_activity',
    ];

    protected Collection $divisionIds;

    protected ?Closure $onUpdate = null;

    protected ?Closure $onAdd = null;

    protected ?Closure $onRemove = null;

    protected array $stats = [
        'added' => 0,
        'updated' => 0,
        'removed' => 0,
        'errors' => 0,
    ];

    protected ?string $lastError = null;

    public function __construct(
        protected ?GetDivisionInfo $divisionInfo = null
    ) {}

    public function onUpdate(callable $callback): self
    {
        $this->onUpdate = $callback;

        return $this;
    }

    public function onAdd(callable $callback): self
    {
        $this->onAdd = $callback;

        return $this;
    }

    public function onRemove(callable $callback): self
    {
        $this->onRemove = $callback;

        return $this;
    }

    public function sync(): bool
    {
        $syncData = $this->fetchSyncData();

        if ($syncData->isEmpty()) {
            Log::critical('MEMBER SYNC - No data available from forum');

            return false;
        }

        $this->divisionIds = $this->getDivisionIds();

        $forumMemberMap = $syncData->keyBy('userid');

        $this->syncExistingMembers($forumMemberMap);
        $this->addNewMembers($forumMemberMap);

        $this->logStats();

        return true;
    }

    public function getStats(): array
    {
        return $this->stats;
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    protected function fetchSyncData(): Collection
    {
        try {
            $info = $this->divisionInfo ?? new GetDivisionInfo;

            return collect($info->data ?? []);
        } catch (Exception $exception) {
            $this->lastError = $exception->getMessage();
            Log::critical('MEMBER SYNC - Failed to fetch data: ' . $this->lastError);

            return collect();
        }
    }

    protected function getDivisionIds(): Collection
    {
        return cache()->remember('division_ids', 60 * 60, function () {
            return Division::active()->pluck('name', 'id')->flip();
        });
    }

    protected function syncExistingMembers(Collection $forumMemberMap): void
    {
        $pendingRequestIds = MemberRequest::pending()->pluck('member_id');

        $members = Member::whereNotIn('division_id', [0])
            ->whereNotIn('clan_id', $pendingRequestIds)
            ->get();

        foreach ($members as $member) {
            $forumData = $forumMemberMap->get($member->clan_id);

            if (! $forumData) {
                $this->handleMemberRemoval($member);

                continue;
            }

            $this->updateMemberIfChanged($member, (object) $forumData);
        }
    }

    protected function handleMemberRemoval(Member $member): void
    {
        if ($this->onRemove) {
            ($this->onRemove)($member->name, $member->clan_id);
        }

        try {
            $member->division->notify(new NotifyDivisionMemberRemoved(
                member: $member,
                remover: null,
                removalReason: null
            ));
        } catch (Exception $exception) {
            Log::error('Failed to send removal notification', [
                'division' => $member->division->name,
                'member' => $member->name,
                'notification' => NotifyDivisionMemberRemoved::class,
                'error' => $exception->getMessage(),
            ]);
        }

        $member->reset();

        $this->clearLeadershipAssignments($member);

        if ($user = $member->user) {
            $user->update(['role' => Role::MEMBER]);
        }

        $this->stats['removed']++;
    }

    protected function clearLeadershipAssignments(Member $member): void
    {
        Squad::whereLeaderId($member->clan_id)
            ->update(['leader_id' => null]);

        Platoon::whereLeaderId($member->clan_id)
            ->update(['leader_id' => null]);
    }

    protected function updateMemberIfChanged(Member $member, object $forumData): void
    {
        if ($forumData->aoddivision === 'None') {
            return;
        }

        try {
            $newData = $this->transformForumData($forumData);
        } catch (Exception $exception) {
            Log::error('Sync error during data transformation', [
                'member_id' => $member->clan_id,
                'exception' => $exception->getMessage(),
            ]);
            $this->stats['errors']++;

            return;
        }

        $oldData = $this->extractMemberData($member);
        $updates = $this->calculateUpdates($oldData, $newData);

        if (empty($updates)) {
            return;
        }

        if ($this->onUpdate) {
            ($this->onUpdate)($member->name, array_keys($updates));
        }

        $this->applyUpdates($member, $updates, $oldData, $newData);
        $this->stats['updated']++;
    }

    protected function extractMemberData(Member $member): array
    {
        return [
            'allow_pm' => $member->allow_pm,
            'discord' => $member->discord,
            'discord_id' => (int) $member->discord_id,
            'division_id' => $member->division_id,
            'name' => $member->name,
            'posts' => $member->posts,
            'privacy_flag' => $member->privacy_flag,
            'ts_unique_id' => $member->ts_unique_id,
            'last_voice_status' => $member->last_voice_status?->value,
            'rank' => $member->rank->value,
            'last_activity' => $this->extractTimestamp($member->last_activity),
            'last_voice_activity' => $this->extractTimestamp($member->last_voice_activity),
        ];
    }

    protected function extractTimestamp($datetime): ?int
    {
        if (! $datetime) {
            return null;
        }

        $timestamp = $datetime->timestamp;

        if ($timestamp < 0) {
            return null;
        }

        return $timestamp;
    }

    protected function transformForumData(object $forumData): array
    {
        return [
            'allow_pm' => $forumData->allow_pm,
            'discord' => $forumData->discordtag,
            'discord_id' => $this->normalizeDiscordId($forumData->discordid),
            'division_id' => $this->divisionIds[$forumData->aoddivision],
            'join_date' => $forumData->joindate,
            'name' => str_replace('AOD_', '', $forumData->username),
            'posts' => $forumData->postcount,
            'privacy_flag' => $forumData->allow_export === 'yes' ? 1 : 0,
            'ts_unique_id' => $forumData->tsid,
            'last_voice_status' => $this->normalizeDiscordStatus($forumData->lastdiscord_status),
            'last_activity' => $this->normalizeTimestamp($forumData->lastactivity),
            'last_voice_activity' => $this->normalizeTimestamp($forumData->lastdiscord_connect),
            'rank' => $this->calculateRank($forumData->aodrankval),
        ];
    }

    protected function calculateRank(int $forumRank): int
    {
        $rank = $forumRank - self::FORUM_RANK_OFFSET;

        return max(1, $rank);
    }

    protected function normalizeDiscordStatus(?string $status): string
    {
        if (empty($status)) {
            return 'never_configured';
        }

        return $status;
    }

    protected function normalizeDiscordId($discordId): int
    {
        if (empty($discordId)) {
            return 0;
        }

        return (int) $discordId;
    }

    protected function normalizeTimestamp($timestamp): ?int
    {
        if (empty($timestamp) || $timestamp === '0') {
            return null;
        }

        return (int) $timestamp;
    }

    protected function calculateUpdates(array $oldData, array $newData): array
    {
        $updates = [];

        foreach (self::SYNCABLE_FIELDS as $field) {
            if (! array_key_exists($field, $newData)) {
                continue;
            }

            $oldValue = $oldData[$field] ?? null;
            $newValue = $newData[$field] ?? null;

            if ($oldValue != $newValue) {
                $updates[$field] = $newValue;
            }
        }

        return $updates;
    }

    protected function applyUpdates(Member $member, array $updates, array $oldData, array $newData): void
    {
        if (isset($updates['name']) && $user = $member->user) {
            Log::debug('Username change detected', [
                'old' => $oldData['name'],
                'new' => $newData['name'],
            ]);
            $user->update(['name' => $updates['name']]);
        }

        $member->update($updates);
    }

    protected function addNewMembers(Collection $forumMemberMap): void
    {
        $existingIds = Member::where('division_id', '!=', 0)
            ->pluck('clan_id')
            ->flip();

        $newMembers = $forumMemberMap
            ->filter(fn ($m) => $m['aoddivision'] !== 'None')
            ->filter(fn ($m) => ! $existingIds->has($m['userid']));

        foreach ($newMembers as $forumMember) {
            $this->createMember((object) $forumMember);
        }
    }

    protected function createMember(object $forumMember): void
    {
        try {
            $data = $this->transformForumData($forumMember);

            Member::updateOrCreate(
                ['clan_id' => $forumMember->userid],
                $data
            );

            if ($this->onAdd) {
                ($this->onAdd)($forumMember->username, $forumMember->userid);
            }

            Log::info('Member added', [
                'name' => $forumMember->username,
                'id' => $forumMember->userid,
            ]);

            $this->stats['added']++;
        } catch (Exception $exception) {
            Log::error('Failed to create member', [
                'user' => $forumMember->userid,
                'exception' => $exception->getMessage(),
            ]);
            $this->stats['errors']++;
        }
    }

    protected function logStats(): void
    {
        Log::info('Member sync completed', $this->stats);
    }
}
