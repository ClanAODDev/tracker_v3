<?php

namespace Tests\Feature;

use App\Models\Division;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

final class DivisionApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->officer()->create();
    }

    #[Test]
    public function unauthenticated_requests_are_rejected()
    {
        $this->json('get', route('v1.divisions.index'))
            ->assertUnauthorized();
    }

    #[Test]
    public function authenticated_requests_succeed()
    {
        Sanctum::actingAs($this->user, ['division:read']);

        $this->json('get', route('v1.divisions.index'))
            ->assertOk();
    }

    #[Test]
    public function an_inactive_division_should_return_404_not_found()
    {
        Sanctum::actingAs($this->user, ['division:read']);

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

    #[Test]
    public function a_division_with_a_shutdown_date_should_not_appear_in_the_divisions_endpoint()
    {
        Sanctum::actingAs($this->user, ['division:read']);

        $response = $this->json('get', route('v1.divisions.index'));

        $response->assertJson(fn (AssertableJson $json) => $json->has('data', 1));

        $divisionShuttingDown = Division::factory(['shutdown_at' => now()->addDays(45)])->create();

        $response = $this->json('get', route('v1.divisions.index'));

        $response->assertDontSee($divisionShuttingDown->name);
    }
}
