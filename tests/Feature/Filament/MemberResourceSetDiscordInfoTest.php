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
    public function sr_ldr_can_clear_forum_discord_info(): void
    {
        $division = $this->createActiveDivision();
        $srLdr    = $this->createSeniorLeader($division);
        $member   = $this->createMember([
            'division_id' => $division->id,
            'discord_id'  => '123456789012345678',
            'discord'     => 'existingusername',
        ]);

        $this->mock(ForumProcedureService::class, function ($mock) use ($member) {
            $mock->shouldReceive('clearDiscordInfo')
                ->once()
                ->with($member->clan_id)
                ->andReturn(null);
        });

        $this->actingAs($srLdr);

        Livewire::test(EditMember::class, ['record' => $member->getRouteKey()])
            ->callAction('setDiscordInfo')
            ->assertHasNoActionErrors();

        $member->refresh();
        $this->assertSame('123456789012345678', $member->discord_id);
        $this->assertSame('existingusername', $member->discord);
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
