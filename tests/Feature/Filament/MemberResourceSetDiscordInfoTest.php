<?php

namespace Tests\Feature\Filament;

use App\Filament\Mod\Resources\MemberResource\Pages\ListMembers;
use App\Services\ForumProcedureService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class MemberResourceSetDiscordInfoTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function sr_ldr_can_set_discord_info(): void
    {
        $division = $this->createActiveDivision();
        $srLdr    = $this->createSeniorLeader($division);
        $member   = $this->createMember(['division_id' => $division->id, 'discord_id' => null, 'discord' => null]);

        $this->mock(ForumProcedureService::class, function ($mock) use ($member) {
            $mock->shouldReceive('setDiscordInfo')
                ->once()
                ->with($member->clan_id, '123456789012345678', 'newusername')
                ->andReturn(null);
        });

        $this->actingAs($srLdr);

        Livewire::test(ListMembers::class)
            ->callTableAction('setDiscordInfo', $member, data: [
                'discord_id'  => '123456789012345678',
                'discord_tag' => 'newusername',
            ])
            ->assertHasNoTableActionErrors();

        $member->refresh();
        $this->assertSame('123456789012345678', $member->discord_id);
        $this->assertSame('newusername', $member->discord);
    }

    #[Test]
    public function discord_id_must_be_numeric(): void
    {
        $division = $this->createActiveDivision();
        $srLdr    = $this->createSeniorLeader($division);
        $member   = $this->createMember(['division_id' => $division->id]);

        $this->actingAs($srLdr);

        Livewire::test(ListMembers::class)
            ->callTableAction('setDiscordInfo', $member, data: [
                'discord_id'  => 'not-a-snowflake',
                'discord_tag' => 'someuser',
            ])
            ->assertHasTableActionErrors(['discord_id']);
    }

    #[Test]
    public function member_role_cannot_see_set_discord_info_action(): void
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser(['division_id' => $division->id]);
        $member   = $this->createMember(['division_id' => $division->id]);

        $this->actingAs($user);

        Livewire::test(ListMembers::class)
            ->assertTableActionHidden('setDiscordInfo', $member);
    }
}
