<?php

namespace Tests\Feature\Authorization;

use App\Enums\DivisionMemberFieldType;
use App\Enums\Position;
use App\Enums\Rank;
use App\Enums\Role;
use App\Models\Division;
use App\Models\DivisionMemberField;
use App\Models\Member;
use App\Models\Platoon;
use App\Models\Squad;
use App\Models\User;
use App\Services\Units\LegacyUnitSync;

class PermissionWorld
{
    public const ACTORS = [
        'admin',
        'developer',
        'admin_viewing_as_officer',
        'senior_leader',
        'commanding_officer',
        'executive_officer',
        'officer',
        'platoon_leader',
        'squad_leader',
        'member',
        'banned',
        'pending',
    ];

    private const SQUAD_PLATOONS = ['A1a' => 'A1', 'A1b' => 'A1', 'A2a' => 'A2', 'B1a' => 'B1'];

    public const TARGETS = [
        'self',
        'same_squad',
        'same_platoon_other_squad',
        'other_platoon',
        'unassigned',
        'other_division',
        'higher_rank',
        'part_timer',
    ];

    public Division $divisionA;

    public Division $divisionB;

    /** @var array<string, Platoon> */
    public array $platoons = [];

    /** @var array<string, Squad> */
    public array $squads = [];

    /** @var array<string, User> */
    public array $users = [];

    /** @var array<string, Member> */
    public array $targets = [];

    /** @var array<string, DivisionMemberField> */
    public array $fields = [];

    public Role $unitLeaderRole = Role::OFFICER;

    public static function build(Role $unitLeaderRole = Role::OFFICER): self
    {
        $world                 = new self;
        $world->unitLeaderRole = $unitLeaderRole;
        $world->buildOrganisation();
        $world->buildActors();
        $world->buildTargets();
        $world->buildFields();

        return $world;
    }

    public function impersonatedRole(string $actor): ?Role
    {
        return $actor === 'admin_viewing_as_officer' ? Role::OFFICER : null;
    }

    public function target(string $actor, string $target): ?Member
    {
        return $target === 'self' ? $this->users[$actor]->member : $this->targets[$target];
    }

    private function buildOrganisation(): void
    {
        $this->divisionA = Division::factory()->withoutHandles()->create(['name' => 'Division A', 'active' => true]);
        $this->divisionB = Division::factory()->withoutHandles()->create(['name' => 'Division B', 'active' => true]);

        $this->platoons['A1'] = Platoon::factory()->create(['division_id' => $this->divisionA->id, 'name' => 'A1', 'leader_id' => 0]);
        $this->platoons['A2'] = Platoon::factory()->create(['division_id' => $this->divisionA->id, 'name' => 'A2', 'leader_id' => 0]);
        $this->platoons['B1'] = Platoon::factory()->create(['division_id' => $this->divisionB->id, 'name' => 'B1', 'leader_id' => 0]);

        foreach (self::SQUAD_PLATOONS as $squad => $platoon) {
            $this->squads[$squad] = Squad::factory()->create([
                'platoon_id' => $this->platoons[$platoon]->id,
                'name'       => $squad,
                'leader_id'  => 0,
            ]);
        }
    }

    private function buildActors(): void
    {
        $this->actor('admin', Role::ADMIN, Position::CLAN_ADMIN, Rank::SERGEANT_MAJOR);
        $this->actor('developer', Role::MEMBER, Position::MEMBER, Rank::PRIVATE, developer: true);
        $this->actor('admin_viewing_as_officer', Role::ADMIN, Position::CLAN_ADMIN, Rank::SERGEANT_MAJOR);
        $this->actor('senior_leader', Role::SENIOR_LEADER, Position::MEMBER, Rank::SERGEANT);
        $this->actor('commanding_officer', Role::SENIOR_LEADER, Position::COMMANDING_OFFICER, Rank::MASTER_SERGEANT);
        $this->actor('executive_officer', Role::SENIOR_LEADER, Position::EXECUTIVE_OFFICER, Rank::STAFF_SERGEANT);
        $this->actor('officer', Role::OFFICER, Position::MEMBER, Rank::CORPORAL, squad: 'A2a');
        $this->actor('platoon_leader', $this->unitLeaderRole, Position::PLATOON_LEADER, Rank::CORPORAL, platoon: 'A1');
        $this->actor('squad_leader', $this->unitLeaderRole, Position::SQUAD_LEADER, Rank::LANCE_CORPORAL, squad: 'A1a');
        $this->actor('member', Role::MEMBER, Position::MEMBER, Rank::PRIVATE_FIRST_CLASS, squad: 'A1a');
        $this->actor('banned', Role::BANNED, Position::MEMBER, Rank::PRIVATE, squad: 'A1a');

        $this->users['pending'] = User::factory()->create([
            'name'      => 'pending',
            'member_id' => null,
            'role'      => Role::MEMBER,
            'developer' => false,
        ]);

        $this->platoons['A1']->update(['leader_id' => $this->users['platoon_leader']->member->clan_id]);
        $this->squads['A1a']->update(['leader_id' => $this->users['squad_leader']->member->clan_id]);
        app(LegacyUnitSync::class)->syncPlatoon($this->platoons['A1']->id);
        app(LegacyUnitSync::class)->syncSquad($this->squads['A1a']->id);
    }

    private function buildTargets(): void
    {
        $this->targets['same_squad']               = $this->member('same_squad', Rank::PRIVATE_FIRST_CLASS, squad: 'A1a');
        $this->targets['same_platoon_other_squad'] = $this->member('same_platoon_other_squad', Rank::PRIVATE_FIRST_CLASS, squad: 'A1b');
        $this->targets['other_platoon']            = $this->member('other_platoon', Rank::PRIVATE_FIRST_CLASS, squad: 'A2a');
        $this->targets['unassigned']               = $this->member('unassigned', Rank::PRIVATE_FIRST_CLASS);
        $this->targets['other_division']           = $this->member('other_division', Rank::PRIVATE_FIRST_CLASS, squad: 'B1a');
        $this->targets['higher_rank']              = $this->member('higher_rank', Rank::SERGEANT_MAJOR, squad: 'A1a');
        $this->targets['part_timer']               = $this->member('part_timer', Rank::PRIVATE_FIRST_CLASS, squad: 'B1a');

        $this->divisionA->partTimeMembers()->attach($this->targets['part_timer']->id);
    }

    private function buildFields(): void
    {
        foreach (['self_editable_field' => true, 'leader_only_field' => false] as $key => $selfEditable) {
            $this->fields[$key] = DivisionMemberField::create([
                'division_id'   => $this->divisionA->id,
                'key'           => $key,
                'label'         => $key,
                'type'          => DivisionMemberFieldType::TEXT,
                'self_editable' => $selfEditable,
            ]);
        }
    }

    private function actor(
        string $name,
        Role $role,
        Position $position,
        Rank $rank,
        ?string $platoon = null,
        ?string $squad = null,
        bool $developer = false,
    ): void {
        $member = $this->member($name, $rank, $platoon, $squad, $position);

        $this->users[$name] = User::factory()->create([
            'name'      => $name,
            'member_id' => $member->id,
            'role'      => $role,
            'developer' => $developer,
        ]);
    }

    private function member(
        string $name,
        Rank $rank,
        ?string $platoon = null,
        ?string $squad = null,
        Position $position = Position::MEMBER,
    ): Member {
        $squadModel   = $squad ? $this->squads[$squad] : null;
        $platoonModel = $this->platoons[$squad ? self::SQUAD_PLATOONS[$squad] : $platoon] ?? null;
        $division     = $platoonModel ? Division::find($platoonModel->division_id) : $this->divisionA;

        return Member::factory()->create([
            'name'        => $name,
            'rank'        => $rank,
            'position'    => $position,
            'division_id' => $division->id,
            'platoon_id'  => $platoonModel?->id ?? 0,
            'squad_id'    => $squadModel?->id ?? 0,
        ]);
    }
}
