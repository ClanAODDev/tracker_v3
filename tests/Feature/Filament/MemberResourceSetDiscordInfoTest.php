<?php

namespace Tests\Feature\Filament;

use App\Filament\Mod\Resources\MemberResource\Pages\EditMember;
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

        Livewire::test(EditMember::class, ['record' => $member->getRouteKey()])
            ->callAction('setDiscordInfo', data: [
                'discord_id'  => '123456789012345678',
                'discord_tag' => 'newusername',
            ])
            ->assertHasNoActionErrors();

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

        Livewire::test(EditMember::class, ['record' => $member->getRouteKey()])
            ->callAction('setDiscordInfo', data: [
                'discord_id'  => 'not-a-snowflake',
                'discord_tag' => 'someuser',
            ])
            ->assertHasActionErrors(['discord_id']);
    }

    #[Test]
    public function member_role_cannot_reach_the_edit_page_at_all(): void
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser(['division_id' => $division->id]);
        $member   = $this->createMember(['division_id' => $division->id]);

        $this->actingAs($user)
            ->get(route('filament.mod.resources.members.edit', $member))
            ->assertForbidden();
    }
}
