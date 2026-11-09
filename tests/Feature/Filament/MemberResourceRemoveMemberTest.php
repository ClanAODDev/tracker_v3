<?php

namespace Tests\Feature\Filament;

use App\Filament\Mod\Resources\MemberResource\Pages\EditMember;
use App\Jobs\RemoveClanMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class MemberResourceRemoveMemberTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function removal_is_blocked_when_the_name_is_not_forum_safe(): void
    {
        Bus::fake();
        Notification::fake();

        $division = $this->createActiveDivision();
        $srLdr    = $this->createSeniorLeader($division);
        $member   = $this->createMember(['division_id' => $division->id, 'name' => 'Bad<Name']);

        $this->actingAs($srLdr);

        Livewire::test(EditMember::class, ['record' => $member->getRouteKey()])
            ->callAction('remove_member', ['removal_reason' => 'Inactive'])
            ->assertNotified('Cannot Remove Member');

        Bus::assertNotDispatched(RemoveClanMember::class);
        $this->assertSame($division->id, $member->fresh()->division_id);
    }
}
