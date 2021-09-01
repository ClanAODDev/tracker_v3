<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
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
        $activeDivision = Division::factory(['abbreviation' => 'unique'])
            ->create();

        $this->json('get', route('v1.divisions.show', $activeDivision->slug))
            ->assertOk();

        $inactiveDivision = Division::factory([
            'abbreviation' => 'foobar',
            'name' => 'Baz Buzz',
            'active' => false,
        ])->create();

        $this->json('get', route('v1.divisions.show', $inactiveDivision->slug))->assertNotFound();
    }

    /** @test */
    public function a_division_with_a_shutdown_date_should_not_appear_in_the_divisions_endpoint()
    {
        $response = $this->json('get', route('v1.divisions.index'));

        // we should have one division, per setUp
        $response->assertJson(fn(AssertableJson $json) => $json->has('data', 1));

        $divisionShuttingDown = Division::factory(['shutdown_at' => now()->addDays(45)])->create();

        $response = $this->json('get', route('v1.divisions.index'));

        $response->assertDontSee($divisionShuttingDown->name);
    }
}
