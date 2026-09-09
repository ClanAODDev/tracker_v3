<?php

namespace Tests\Feature\Controllers;

use App\Enums\Rank;
use App\Models\TrainingModule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class TrainingModuleCompletionTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function completion_button_shows_for_deep_linked_trainee(): void
    {
        $srLdr  = $this->createSeniorLeader(memberAttributes: ['rank' => Rank::STAFF_SERGEANT]);
        $module = TrainingModule::create([
            'name'                 => 'General Training',
            'slug'                 => 'general',
            'is_active'            => true,
            'show_completion_form' => true,
        ]);
        $trainee = $this->createMember();

        $this->actingAs($srLdr)
            ->get(route('training.show', ['slug' => $module->slug, 'clan_id' => $trainee->clan_id]))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('training/module')
                ->where('module.showCompletionForm', true)
                ->where('trainee.name', $trainee->name)
                ->where('trainee.clanId', $trainee->clan_id));
    }

    #[Test]
    public function completion_button_hidden_without_a_deep_linked_trainee(): void
    {
        $srLdr  = $this->createSeniorLeader(memberAttributes: ['rank' => Rank::STAFF_SERGEANT]);
        $module = TrainingModule::create([
            'name'                 => 'General Training',
            'slug'                 => 'general',
            'is_active'            => true,
            'show_completion_form' => true,
        ]);

        $this->actingAs($srLdr)
            ->get(route('training.show', ['slug' => $module->slug]))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('training/module')
                ->where('trainee', null));
    }

    #[Test]
    public function completion_button_hidden_when_module_does_not_show_completion_form(): void
    {
        $srLdr  = $this->createSeniorLeader(memberAttributes: ['rank' => Rank::STAFF_SERGEANT]);
        $module = TrainingModule::create([
            'name'                 => 'General Training',
            'slug'                 => 'general',
            'is_active'            => true,
            'show_completion_form' => false,
        ]);
        $trainee = $this->createMember();

        $this->actingAs($srLdr)
            ->get(route('training.show', ['slug' => $module->slug, 'clan_id' => $trainee->clan_id]))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('training/module')
                ->where('module.showCompletionForm', false));
    }

    #[Test]
    public function submitting_completion_form_updates_trainee_record(): void
    {
        $srLdr   = $this->createSeniorLeader(memberAttributes: ['rank' => Rank::STAFF_SERGEANT]);
        $trainee = $this->createMember(['last_trained_at' => null, 'last_trained_by' => null]);

        $this->actingAs($srLdr)
            ->post(route('training.update'), [
                'module'  => 'general',
                'clan_id' => $trainee->clan_id,
            ])
            ->assertRedirect('home');

        $trainee->refresh();
        $this->assertNotNull($trainee->last_trained_at);
        $this->assertSame($srLdr->member->clan_id, $trainee->last_trained_by);
    }
}
