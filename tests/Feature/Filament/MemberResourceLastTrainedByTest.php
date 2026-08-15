<?php

namespace Tests\Feature\Filament;

use App\Filament\Admin\Resources\MemberResource\Pages\EditMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class MemberResourceLastTrainedByTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function last_trained_by_can_be_set_via_searchable_select(): void
    {
        $this->actingAs($this->createAdmin());

        $member  = $this->createMember();
        $trainer = $this->createMember();

        Livewire::test(EditMember::class, ['record' => $member->getRouteKey()])
            ->fillForm(['last_trained_by' => $trainer->clan_id])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame($trainer->clan_id, $member->fresh()->last_trained_by);
        $this->assertTrue($member->fresh()->trainer->is($trainer));
    }
}
