<?php

namespace Tests\Feature\Filament;

use App\Filament\Admin\Resources\AwardResource\Pages\CreateAward;
use App\Filament\Admin\Resources\AwardResource\Pages\EditAward;
use App\Models\Award;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class AwardNameLockTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel('admin');
        $this->actingAs($this->createAdmin());
    }

    #[Test]
    public function the_name_is_locked_and_explained_on_edit(): void
    {
        $award = Award::factory()->create(['name' => '5 Years of Service']);

        Livewire::test(EditAward::class, ['record' => $award->getRouteKey()])
            ->assertFormFieldDisabled('name')
            ->assertSee('Locked because code finds some awards by name');
    }

    #[Test]
    public function other_fields_can_be_saved_without_touching_the_name(): void
    {
        $award = Award::factory()->create(['name' => '5 Years of Service', 'description' => 'Old']);

        Livewire::test(EditAward::class, ['record' => $award->getRouteKey()])
            ->fillForm(['description' => 'New'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('5 Years of Service', $award->fresh()->name);
        $this->assertSame('New', $award->fresh()->description);
    }

    #[Test]
    public function the_name_is_editable_on_create(): void
    {
        Livewire::test(CreateAward::class)->assertFormFieldEnabled('name');
    }
}
