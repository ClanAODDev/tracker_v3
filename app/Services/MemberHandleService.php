<?php

namespace App\Services;

use App\Models\Handle;
use App\Models\Member;
use App\Models\MemberHandle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MemberHandleService
{
    public static function getGroupedHandles(Member $member): array
    {
        return $member->memberHandles()
            ->with('handle')
            ->get()
            ->groupBy('handle_id')
            ->sortBy(function ($group, $handleId) {
                $handle = Handle::find($handleId);

                return $handle?->label ?? '';
            })
            ->map(function ($group) {
                return [
                    'uuid' => (string) Str::uuid(),
                    'handle_id' => $group->first()->handle_id,
                    'handles' => $group
                        ->sortByDesc('primary')
                        ->values()
                        ->map(function ($mh) {
                            return [
                                'uuid' => (string) Str::uuid(),
                                'id' => $mh->id,
                                'value' => $mh->value,
                                'primary' => (bool) $mh->primary,
                            ];
                        })
                        ->toArray(),
                ];
            })
            ->values()
            ->toArray();
    }

    public static function saveHandles(Model $member, array $handleGroups): void
    {
        foreach ($handleGroups as &$group) {
            $primaries = collect($group['handles'])->where('primary', true);

            if ($primaries->count() === 0 && ! empty($group['handles'])) {
                $group['handles'][0]['primary'] = true;
            } elseif ($primaries->count() > 1) {
                $firstPrimary = $primaries->keys()->first();
                foreach ($group['handles'] as $i => &$handle) {
                    $handle['primary'] = ($i === $firstPrimary);
                }
            }
        }
        unset($group);

        $flattened = [];
        foreach ($handleGroups as $group) {
            $handleId = $group['handle_id'] ?? null;
            if (! $handleId) {
                continue;
            }

            foreach (($group['handles'] ?? []) as $h) {
                if (empty($h['value'])) {
                    continue;
                }
                $flattened[] = [
                    'id' => $h['id'] ?? null,
                    'handle_id' => $handleId,
                    'value' => $h['value'],
                    'primary' => (bool) $h['primary'],
                ];
            }
        }

        $existingIds = \DB::table('handle_member')
            ->where('member_id', $member->id)
            ->pluck('id')
            ->toArray();

        $formIds = collect($flattened)->pluck('id')->filter()->toArray();
        $idsToDelete = array_diff($existingIds, $formIds);

        if (! empty($idsToDelete)) {
            MemberHandle::where('member_id', $member->id)
                ->whereIn('id', $idsToDelete)
                ->delete();
        }

        foreach ($flattened as $row) {
            if (! empty($row['id'])) {
                MemberHandle::where('id', $row['id'])
                    ->update([
                        'handle_id' => $row['handle_id'],
                        'value' => $row['value'],
                        'primary' => $row['primary'],
                        'updated_at' => now(),
                    ]);
            } else {
                MemberHandle::create([
                    'member_id' => $member->id,
                    'handle_id' => $row['handle_id'],
                    'value' => $row['value'],
                    'primary' => $row['primary'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
