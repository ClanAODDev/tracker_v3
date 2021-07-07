<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DivisionApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->officer()->create();

        Sanctum::actingAs($this->user, ['*']);

        $this->json('get', route('v1.divisions.index'))
            ->assertStatus(200);
    }

    /** @test */
    public function an_inactive_division_should_return_404_not_found()
    {
        $activeDivision = Division::factory()->create();

        $this->json('get', route('v1.divisions.show', $activeDivision))
            ->assertOk();

        $inactiveDivision = Division::factory()->inactive()->create();

        $this->json('get', route('v1.divisions.show', $inactiveDivision))
            ->assertNotFound();
    }
}
