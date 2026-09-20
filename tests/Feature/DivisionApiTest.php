<?php

namespace Tests\Feature;

use App\Enums\Position;
use App\Models\Division;
use App\Models\Member;
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
            'name'         => 'Baz Buzz',
            'active'       => false,
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

    #[Test]
    public function division_read_ability_does_not_expose_leadership_discord_ids()
    {
        Sanctum::actingAs($this->user, ['division:read']);

        $division = Division::factory()->create();

        Member::factory()->create([
            'division_id' => $division->id,
            'position'    => Position::COMMANDING_OFFICER,
            'discord_id'  => 123456789012345678,
        ]);

        $response = $this->json('get', route('v1.divisions.show', $division->slug));

        $response->assertOk();
        $response->assertJsonMissingPath('data.division.leadership.0.discord_id');
        $response->assertDontSee('123456789012345678');
    }

    #[Test]
    public function division_read_advanced_ability_still_exposes_member_discord_ids()
    {
        Sanctum::actingAs($this->user, ['division:read', 'division:read-advanced']);

        $division = Division::factory()->create();

        Member::factory()->create([
            'division_id' => $division->id,
            'discord_id'  => 123456789012345678,
        ]);

        $response = $this->json('get', route('v1.divisions.show', $division->slug) . '?include_members=1');

        $response->assertOk();
        $response->assertSee('123456789012345678');
    }

    #[Test]
    public function division_show_can_be_looked_up_by_guid()
    {
        Sanctum::actingAs($this->user, ['division:read']);

        $division = Division::factory()->create();

        $response = $this->json('get', route('v1.divisions.show', $division->guid));

        $response->assertOk();
        $response->assertJsonPath('data.division.slug', $division->slug);
    }

    #[Test]
    public function division_update_can_be_looked_up_by_guid()
    {
        Sanctum::actingAs($this->user, ['division:write']);

        $division = Division::factory()->create();

        $this->json('post', route('v1.divisions.update', $division->guid), [
            'division_channel' => '123456789012345678',
        ])->assertStatus(202);

        $this->assertSame('123456789012345678', $division->fresh()->division_channel);
    }

    #[Test]
    public function division_show_includes_its_immutable_guid()
    {
        Sanctum::actingAs($this->user, ['division:read']);

        $division = Division::factory()->create();

        $response = $this->json('get', route('v1.divisions.show', $division->slug));

        $response->assertOk();
        $response->assertJsonPath('data.division.guid', $division->guid);
    }

    #[Test]
    public function division_write_ability_can_update_the_division_channel()
    {
        Sanctum::actingAs($this->user, ['division:write']);

        $division = Division::factory()->create();

        $this->json('post', route('v1.divisions.update', $division->slug), [
            'division_channel' => '123456789012345678',
        ])->assertStatus(202);

        $this->assertSame('123456789012345678', $division->fresh()->division_channel);
    }

    #[Test]
    public function division_write_ability_can_update_officer_and_member_channels()
    {
        Sanctum::actingAs($this->user, ['division:write']);

        $division = Division::factory()->create();

        $this->json('post', route('v1.divisions.update', $division->slug), [
            'officer_channel' => '509933611009441803',
            'member_channel'  => '509933610766434314',
        ])->assertStatus(202);

        $division->refresh();
        $this->assertSame('509933611009441803', $division->settings()->get('officer_channel'));
        $this->assertSame('509933610766434314', $division->settings()->get('member_channel'));
    }

    #[Test]
    public function officer_and_member_channels_must_look_like_discord_snowflakes()
    {
        Sanctum::actingAs($this->user, ['division:write']);

        $division = Division::factory()->create();

        $this->json('post', route('v1.divisions.update', $division->slug), [
            'officer_channel' => 'not-a-snowflake',
            'member_channel'  => 'also-not-one',
        ])->assertJsonValidationErrors(['officer_channel', 'member_channel']);
    }

    #[Test]
    public function division_channel_must_look_like_a_discord_snowflake()
    {
        Sanctum::actingAs($this->user, ['division:write']);

        $division = Division::factory()->create();

        $this->json('post', route('v1.divisions.update', $division->slug), [
            'division_channel' => 'not-a-snowflake',
        ])->assertJsonValidationErrors('division_channel');

        $this->assertNull($division->fresh()->division_channel);
    }

    #[Test]
    public function division_read_ability_cannot_update_the_division_channel()
    {
        Sanctum::actingAs($this->user, ['division:read']);

        $division = Division::factory()->create();

        $this->json('post', route('v1.divisions.update', $division->slug), [
            'division_channel' => '123456789012345678',
        ])->assertForbidden();

        $this->assertNull($division->fresh()->division_channel);
    }

    #[Test]
    public function division_read_advanced_ability_exposes_leadership_discord_ids()
    {
        Sanctum::actingAs($this->user, ['division:read', 'division:read-advanced']);

        $division = Division::factory()->create();

        Member::factory()->create([
            'division_id' => $division->id,
            'position'    => Position::COMMANDING_OFFICER,
            'discord_id'  => 123456789012345678,
        ]);

        $response = $this->json('get', route('v1.divisions.show', $division->slug));

        $response->assertOk();
        $response->assertJsonPath('data.division.leadership.0.discord_id', '123456789012345678');
    }
}
