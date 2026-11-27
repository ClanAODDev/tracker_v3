<?php

namespace Tests\Feature\Filament;

use App\Filament\Admin\Resources\TrainingModuleResource\Pages\EditTrainingModule;
use App\Filament\Admin\Resources\TrainingModuleResource\RelationManagers\SectionsRelationManager;
use App\Models\TrainingModule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class TrainingSectionReorderTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function dragging_sections_saves_the_new_order(): void
    {
        $this->actingAs($this->createAdmin());

        $module = TrainingModule::create(['name' => 'SGT Training', 'slug' => 'sgt-training', 'display_order' => 1]);
        $first  = $module->sections()->create(['title' => 'First', 'content' => 'a', 'display_order' => 1]);
        $second = $module->sections()->create(['title' => 'Second', 'content' => 'b', 'display_order' => 2]);
        $third  = $module->sections()->create(['title' => 'Third', 'content' => 'c', 'display_order' => 3]);

        Livewire::test(SectionsRelationManager::class, ['ownerRecord' => $module, 'pageClass' => EditTrainingModule::class])
            ->assertCanSeeTableRecords([$first, $second, $third], inOrder: true)
            ->call('reorderTable', [(string) $third->id, (string) $first->id, (string) $second->id], (string) $third->id)
            ->assertHasNoErrors();

        $this->assertSame(
            ['Third', 'First', 'Second'],
            $module->sections()->pluck('title')->all(),
        );
    }
}
