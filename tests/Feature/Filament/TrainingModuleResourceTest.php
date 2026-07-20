<?php

namespace Tests\Feature\Filament;

use App\Filament\Admin\Resources\TrainingModuleResource\Pages\ListTrainingModules;
use App\Models\TrainingModule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class TrainingModuleResourceTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function clone_action_duplicates_module_with_new_name_and_slug(): void
    {
        $this->actingAs($this->createAdmin());

        $module = TrainingModule::create([
            'name'          => 'SGT Training',
            'slug'          => 'sgt-training',
            'display_order' => 1,
        ]);

        Livewire::test(ListTrainingModules::class)
            ->callTableAction('clone', $module, data: [
                'name' => 'SGT Training (Copy)',
                'slug' => 'sgt-training-copy',
            ])
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('training_modules', ['slug' => 'sgt-training']);

        $clone = TrainingModule::where('slug', '!=', 'sgt-training')->first();
        $this->assertNotNull($clone);
        $this->assertEquals('SGT Training (Copy)', $clone->name);
        $this->assertEquals('sgt-training-copy', $clone->slug);
    }

    #[Test]
    public function clone_action_modal_prefills_copy_name_and_slug(): void
    {
        $this->actingAs($this->createAdmin());

        $module = TrainingModule::create([
            'name'          => 'SGT Training',
            'slug'          => 'sgt-training',
            'display_order' => 1,
        ]);

        Livewire::test(ListTrainingModules::class)
            ->mountTableAction('clone', $module)
            ->assertTableActionDataSet([
                'name' => 'SGT Training (Copy)',
                'slug' => 'sgt-training-copy',
            ]);
    }

    #[Test]
    public function clone_action_duplicates_sections_and_checkpoints(): void
    {
        $this->actingAs($this->createAdmin());

        $module = TrainingModule::create([
            'name'          => 'SGT Training',
            'slug'          => 'sgt-training',
            'display_order' => 1,
        ]);

        $section = $module->sections()->create([
            'title'         => 'Introduction',
            'content'       => 'Welcome content',
            'display_order' => 1,
        ]);

        $section->checkpoints()->create([
            'label'         => 'Say hello',
            'display_order' => 1,
        ]);
        $section->checkpoints()->create([
            'label'         => 'Explain expectations',
            'display_order' => 2,
        ]);

        Livewire::test(ListTrainingModules::class)
            ->callTableAction('clone', $module, data: [
                'name' => 'SGT Training (Copy)',
                'slug' => 'sgt-training-copy',
            ])
            ->assertHasNoTableActionErrors();

        $clone = TrainingModule::where('slug', 'sgt-training-copy')->firstOrFail();

        $this->assertCount(1, $clone->sections);
        $clonedSection = $clone->sections->first();
        $this->assertEquals('Introduction', $clonedSection->title);
        $this->assertEquals('Welcome content', $clonedSection->content);
        $this->assertNotEquals($section->id, $clonedSection->id);

        $this->assertCount(2, $clonedSection->checkpoints);
        $this->assertEquals(
            ['Say hello', 'Explain expectations'],
            $clonedSection->checkpoints->pluck('label')->all()
        );
    }

    #[Test]
    public function cloning_does_not_affect_original_module_sections(): void
    {
        $this->actingAs($this->createAdmin());

        $module = TrainingModule::create([
            'name'          => 'SGT Training',
            'slug'          => 'sgt-training',
            'display_order' => 1,
        ]);

        $module->sections()->create([
            'title'         => 'Introduction',
            'content'       => 'Welcome content',
            'display_order' => 1,
        ]);

        Livewire::test(ListTrainingModules::class)
            ->callTableAction('clone', $module, data: [
                'name' => 'SGT Training (Copy)',
                'slug' => 'sgt-training-copy',
            ])
            ->assertHasNoTableActionErrors();

        $this->assertCount(1, $module->fresh()->sections);
    }
}
