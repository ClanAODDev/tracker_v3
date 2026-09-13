<?php

namespace Tests\Feature\Filament;

use App\Filament\Mod\Resources\MemberResource\Pages\EditMember;
use App\Models\Handle;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class MemberResourceIngameHandlesTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel('mod');
    }

    #[Test]
    public function sr_ldr_can_save_a_handle_value_matching_its_format(): void
    {
        $division = $this->createActiveDivision();
        $srLdr    = $this->createSeniorLeader($division);
        $member   = $this->createMember(['division_id' => $division->id]);
        $handle   = Handle::create(['label' => 'Steam', 'regex' => '/^[0-9]+$/']);

        $this->actingAs($srLdr);

        Livewire::test(EditMember::class, ['record' => $member->getRouteKey()])
            ->fillForm([
                'handleGroups' => [
                    ['id' => null, 'handle_id' => $handle->id, 'value' => '76561198000000000', 'primary' => true],
                ],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('handle_member', [
            'member_id' => $member->id,
            'handle_id' => $handle->id,
            'value'     => '76561198000000000',
        ]);
    }

    #[Test]
    public function sr_ldr_cannot_save_a_handle_value_that_fails_its_format(): void
    {
        $division = $this->createActiveDivision();
        $srLdr    = $this->createSeniorLeader($division);
        $member   = $this->createMember(['division_id' => $division->id]);
        $handle   = Handle::create([
            'label'      => 'Steam',
            'regex'      => '/^[0-9]+$/',
            'regex_hint' => 'Steam ID must be numeric.',
        ]);

        $this->actingAs($srLdr);

        Livewire::test(EditMember::class, ['record' => $member->getRouteKey()])
            ->fillForm([
                'handleGroups' => [
                    ['id' => null, 'handle_id' => $handle->id, 'value' => 'not-numeric', 'primary' => true],
                ],
            ])
            ->call('save')
            ->assertHasFormErrors(['handleGroups.0.value']);

        $this->assertDatabaseMissing('handle_member', ['member_id' => $member->id]);
    }
}
