<?php

namespace Tests\Feature\Controllers;

use App\Models\Handle;
use App\Models\MemberHandle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class SettingsControllerTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    #[Test]
    public function data_endpoint_returns_the_current_settings_and_member_context()
    {
        $division = $this->createActiveDivision();
        $user     = $this->createMemberWithUser(['division_id' => $division->id]);
        Handle::create(['label' => 'Discord']);

        $this->actingAs($user)
            ->getJson(route('settings.data'))
            ->assertOk()
            ->assertJsonStructure([
                'settings'          => ['disable_animations', 'mobile_nav_side'],
                'member'            => ['name', 'canSyncAvatar', 'canRequestTransfer'],
                'partTimeDivisions' => ['available', 'selected'],
                'handles'           => ['types', 'current'],
                'transferableDivisions',
            ]);
    }

    #[Test]
    public function handle_types_list_hides_disabled_types_not_in_use_but_keeps_ones_the_member_already_has()
    {
        $user           = $this->createMemberWithUser();
        $enabled        = Handle::create(['label' => 'Steam', 'enabled' => true]);
        $disabledUnused = Handle::create(['label' => 'Old Platform', 'enabled' => false]);
        $disabledInUse  = Handle::create(['label' => 'Legacy Platform', 'enabled' => false]);

        MemberHandle::create([
            'member_id' => $user->member->id,
            'handle_id' => $disabledInUse->id,
            'value'     => 'SomeName',
            'primary'   => true,
        ]);

        $response = $this->actingAs($user)->getJson(route('settings.data'))->assertOk();

        $labels = collect($response->json('handles.types'))->pluck('label');

        $this->assertTrue($labels->contains('Steam'));
        $this->assertTrue($labels->contains('Legacy Platform (disabled)'));
        $this->assertFalse($labels->contains('Old Platform'));
        $this->assertFalse($labels->contains('Old Platform (disabled)'));
    }

    #[Test]
    public function update_persists_only_the_supported_appearance_keys()
    {
        $user = $this->createMemberWithUser();

        $this->actingAs($user)
            ->postJson(route('settings.update'), ['disable_animations' => true, 'mobile_nav_side' => 'left'])
            ->assertOk();

        $fresh = $user->fresh();
        $this->assertTrue($fresh->settings()->get('disable_animations'));
        $this->assertSame('left', $fresh->settings()->get('mobile_nav_side'));
    }

    #[Test]
    public function update_rejects_an_invalid_nav_side()
    {
        $user = $this->createMemberWithUser();

        $this->actingAs($user)
            ->postJson(route('settings.update'), ['mobile_nav_side' => 'sideways'])
            ->assertStatus(422);
    }

    #[Test]
    public function theme_can_be_switched_to_light()
    {
        $user = $this->createMemberWithUser();

        $this->actingAs($user)
            ->postJson(route('settings.update'), ['theme' => 'light'])
            ->assertOk();

        $this->assertSame('light', $user->fresh()->settings()->get('theme'));

        $this->actingAs($user)
            ->postJson(route('settings.update'), ['theme' => 'sepia'])
            ->assertStatus(422);
    }

    #[Test]
    public function part_time_divisions_sync_to_active_divisions_only()
    {
        $division = $this->createActiveDivision();
        $partTime = $this->createActiveDivision();
        $user     = $this->createMemberWithUser(['division_id' => $division->id]);

        $this->actingAs($user)
            ->postJson(route('settings.part-time-divisions'), ['divisions' => [$partTime->id, 999999]])
            ->assertOk()
            ->assertJson(['count' => 1]);

        $this->assertTrue($user->member->partTimeDivisions()->where('divisions.id', $partTime->id)->exists());
    }

    #[Test]
    public function settings_data_requires_authentication()
    {
        $this->getJson(route('settings.data'))->assertUnauthorized();
    }

    #[Test]
    public function ingame_handles_are_saved_when_value_matches_the_handle_format()
    {
        $user   = $this->createMemberWithUser();
        $handle = Handle::create(['label' => 'Steam', 'regex' => '/^[0-9]+$/']);

        $this->actingAs($user)
            ->postJson(route('settings.ingame-handles'), [
                'handles' => [
                    ['id' => '', 'handle_id' => $handle->id, 'value' => '76561198000000000', 'primary' => true],
                ],
            ])
            ->assertOk()
            ->assertJson(['count' => 1]);

        $this->assertDatabaseHas('handle_member', [
            'member_id' => $user->member->id,
            'handle_id' => $handle->id,
            'value'     => '76561198000000000',
        ]);
    }

    #[Test]
    public function ingame_handles_reject_a_value_that_fails_the_handle_format()
    {
        $user   = $this->createMemberWithUser();
        $handle = Handle::create([
            'label'      => 'Steam',
            'regex'      => '/^[0-9]+$/',
            'regex_hint' => 'Steam ID must be numeric.',
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('settings.ingame-handles'), [
                'handles' => [
                    ['id' => '', 'handle_id' => $handle->id, 'value' => 'not-numeric', 'primary' => true],
                ],
            ]);

        $response->assertStatus(422);
        $response->assertJson(['message' => 'Steam ID must be numeric.', 'index' => 0]);
        $this->assertDatabaseMissing('handle_member', ['member_id' => $user->member->id]);
    }

    #[Test]
    public function ingame_handles_error_reports_the_index_of_the_failing_row()
    {
        $user    = $this->createMemberWithUser();
        $steam   = Handle::create(['label' => 'Steam', 'regex' => '/^[0-9]+$/', 'regex_hint' => 'Steam ID must be numeric.']);
        $discord = Handle::create(['label' => 'Discord']);

        $response = $this->actingAs($user)
            ->postJson(route('settings.ingame-handles'), [
                'handles' => [
                    ['id' => '', 'handle_id' => $discord->id, 'value' => 'ValidName', 'primary' => true],
                    ['id' => '', 'handle_id' => $steam->id, 'value' => 'not-numeric', 'primary' => false],
                ],
            ]);

        $response->assertStatus(422);
        $response->assertJson(['message' => 'Steam ID must be numeric.', 'index' => 1]);
        $this->assertDatabaseMissing('handle_member', ['member_id' => $user->member->id]);
    }
}
