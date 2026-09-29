<?php

namespace App\Services;

use App\Models\Division;
use App\Models\Handle;
use App\Models\Member;
use App\Models\MemberHandle;
use Illuminate\Support\Facades\DB;

class MemberHandleService
{
    public function sync(Member $member, array $rows): void
    {
        DB::transaction(function () use ($member, $rows) {
            $rows    = collect($rows);
            $keptIds = $rows->pluck('id')->filter()->all();
            $types   = Handle::whereIn('id', $rows->pluck('handle_id')->filter())->get()->keyBy('id');

            MemberHandle::where('member_id', $member->id)->whereNotIn('id', $keptIds)->delete();

            foreach ($rows->groupBy('handle_id') as $handleId => $rowsOfType) {
                $rowsOfType = $rowsOfType->filter(fn (array $row) => filled($row['handle_id'] ?? null) && filled($row['value'] ?? null))->values();
                $primary    = $rowsOfType->search(fn (array $row) => (bool) ($row['primary'] ?? false));
                $primary    = $primary === false ? $rowsOfType->keys()->last() : $primary;

                foreach ($rowsOfType as $index => $row) {
                    $attributes = [
                        'handle_id' => $handleId,
                        'value'     => $types->get($handleId)?->normalize($row['value']) ?? $row['value'],
                        'primary'   => $index === $primary,
                    ];

                    filled($row['id'] ?? null)
                        ? MemberHandle::where('member_id', $member->id)->whereKey($row['id'])->update($attributes)
                        : MemberHandle::create(['member_id' => $member->id, ...$attributes]);
                }
            }
        });
    }

    public function setPrimary(Member $member, Handle $handle, string $value): void
    {
        $value = $handle->normalize($value);

        DB::transaction(function () use ($member, $handle, $value) {
            $existing = MemberHandle::where('member_id', $member->id)
                ->where('handle_id', $handle->id)
                ->where('value', $value)
                ->first();

            MemberHandle::where('member_id', $member->id)
                ->where('handle_id', $handle->id)
                ->when($existing, fn ($query) => $query->whereKeyNot($existing->id))
                ->update(['primary' => false]);

            if ($existing) {
                $existing->update(['primary' => true]);

                return;
            }

            MemberHandle::create([
                'member_id' => $member->id,
                'handle_id' => $handle->id,
                'value'     => $value,
                'primary'   => true,
            ]);
        });
    }

    public function setForDivision(Member $member, Division $division, array $values): void
    {
        $division->handles
            ->filter(fn (Handle $handle) => filled($values[$handle->id] ?? null))
            ->each(fn (Handle $handle) => $this->setPrimary($member, $handle, $values[$handle->id]));
    }
}
