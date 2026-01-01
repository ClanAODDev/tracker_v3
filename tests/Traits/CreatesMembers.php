<?php

namespace Tests\Traits;

use App\Enums\Position;
use App\Enums\Rank;
use App\Enums\Role;
use App\Models\Division;
use App\Models\Member;
use App\Models\Platoon;
use App\Models\Squad;
use App\Models\User;

trait CreatesMembers
{
    protected function createMember(array $attributes = []): Member
    {
        return Member::factory()->create($attributes);
    }

    protected function createMemberWithUser(array $memberAttributes = [], array $userAttributes = []): User
    {
        $member = $this->createMember($memberAttributes);

        return User::factory()->create(array_merge([
            'member_id' => $member->id,
            'name' => $member->name,
        ], $userAttributes));
    }

    protected function createAdmin(array $memberAttributes = [], array $userAttributes = []): User
    {
        $member = Member::factory()->create(array_merge([
            'rank' => Rank::MASTER_SERGEANT,
            'position' => Position::COMMANDING_OFFICER,
        ], $memberAttributes));

        return User::factory()->create(array_merge([
            'member_id' => $member->id,
            'name' => $member->name,
            'role_id' => Role::ADMIN->value,
            'developer' => true,
        ], $userAttributes));
    }

    protected function createSeniorLeader(?Division $division = null, array $memberAttributes = [], array $userAttributes = []): User
    {
        $division = $division ?? Division::factory()->create();

        $member = Member::factory()->create(array_merge([
            'rank' => Rank::SERGEANT,
            'position' => Position::COMMANDING_OFFICER,
            'division_id' => $division->id,
        ], $memberAttributes));

        return User::factory()->create(array_merge([
            'member_id' => $member->id,
            'name' => $member->name,
            'role_id' => Role::SENIOR_LEADER->value,
        ], $userAttributes));
    }

    protected function createOfficer(?Division $division = null, array $memberAttributes = [], array $userAttributes = []): User
    {
        $division = $division ?? Division::factory()->create();

        $member = Member::factory()->create(array_merge([
            'rank' => Rank::STAFF_SERGEANT,
            'position' => Position::EXECUTIVE_OFFICER,
            'division_id' => $division->id,
        ], $memberAttributes));

        return User::factory()->create(array_merge([
            'member_id' => $member->id,
            'name' => $member->name,
            'role_id' => Role::OFFICER->value,
        ], $userAttributes));
    }

    protected function createSquadLeader(Squad $squad, array $memberAttributes = []): Member
    {
        $member = Member::factory()->ofTypeSquadLeader()->create(array_merge([
            'division_id' => $squad->platoon->division_id,
            'platoon_id' => $squad->platoon_id,
            'squad_id' => $squad->id,
        ], $memberAttributes));

        $squad->update(['leader_id' => $member->clan_id]);

        return $member;
    }

    protected function createPlatoonLeader(Platoon $platoon, array $memberAttributes = []): Member
    {
        $member = Member::factory()->ofTypePlatoonLeader()->create(array_merge([
            'division_id' => $platoon->division_id,
            'platoon_id' => $platoon->id,
        ], $memberAttributes));

        $platoon->update(['leader_id' => $member->clan_id]);

        return $member;
    }

    protected function createCommander(Division $division, array $memberAttributes = []): Member
    {
        return Member::factory()->ofTypeCommander()->create(array_merge([
            'division_id' => $division->id,
            'co_at' => now(),
        ], $memberAttributes));
    }

    protected function createExecutiveOfficer(Division $division, array $memberAttributes = []): Member
    {
        return Member::factory()->ofTypeExecutiveOfficer()->create(array_merge([
            'division_id' => $division->id,
            'xo_at' => now(),
        ], $memberAttributes));
    }

    protected function createMembersInSquad(Squad $squad, int $count = 3, array $attributes = []): \Illuminate\Support\Collection
    {
        return Member::factory()->count($count)->create(array_merge([
            'division_id' => $squad->platoon->division_id,
            'platoon_id' => $squad->platoon_id,
            'squad_id' => $squad->id,
        ], $attributes));
    }

    protected function createMembersInPlatoon(Platoon $platoon, int $count = 3, array $attributes = []): \Illuminate\Support\Collection
    {
        return Member::factory()->count($count)->create(array_merge([
            'division_id' => $platoon->division_id,
            'platoon_id' => $platoon->id,
        ], $attributes));
    }

    protected function createMembersInDivision(Division $division, int $count = 3, array $attributes = []): \Illuminate\Support\Collection
    {
        return Member::factory()->count($count)->create(array_merge([
            'division_id' => $division->id,
        ], $attributes));
    }
}
