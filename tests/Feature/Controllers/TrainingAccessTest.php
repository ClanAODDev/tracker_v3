<?php

namespace Tests\Feature\Controllers;

use App\Enums\Rank;
use App\Models\TrainingModule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class TrainingAccessTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        TrainingModule::create(['name' => 'General Training', 'slug' => 'general', 'is_active' => true]);
        TrainingModule::create(['name' => 'SGT Training', 'slug' => 'sgt', 'is_active' => true]);
    }

    public static function pages(): array
    {
        return [
            'index' => ['training.index', []],
            'show'  => ['training.show', ['slug' => 'general']],
            'sgt'   => ['training.sgt', []],
        ];
    }

    #[Test]
    #[DataProvider('pages')]
    public function trainers_above_sergeant_can_open_training(string $route, array $parameters): void
    {
        $this->actingAs($this->createSeniorLeader(memberAttributes: ['rank' => Rank::STAFF_SERGEANT]))
            ->get(route($route, $parameters))
            ->assertOk();
    }

    #[Test]
    #[DataProvider('pages')]
    public function sergeants_cannot_open_training(string $route, array $parameters): void
    {
        $this->actingAs($this->createSeniorLeader(memberAttributes: ['rank' => Rank::SERGEANT]))
            ->get(route($route, $parameters))
            ->assertForbidden();
    }

    #[Test]
    #[DataProvider('pages')]
    public function users_without_the_training_ability_cannot_open_training(string $route, array $parameters): void
    {
        $this->actingAs($this->createMemberWithUser(memberAttributes: ['rank' => Rank::STAFF_SERGEANT]))
            ->get(route($route, $parameters))
            ->assertForbidden();
    }

    #[Test]
    #[DataProvider('pages')]
    public function guests_are_sent_to_login(string $route, array $parameters): void
    {
        $this->get(route($route, $parameters))->assertRedirect('/login');
    }
}
