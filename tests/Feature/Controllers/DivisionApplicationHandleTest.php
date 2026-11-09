<?php

namespace Tests\Feature\Controllers;

use App\Models\Division;
use App\Models\DivisionApplication;
use App\Models\Handle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Tests\Traits\CreatesDivisions;
use Tests\Traits\CreatesMembers;

class DivisionApplicationHandleTest extends TestCase
{
    use CreatesDivisions;
    use CreatesMembers;
    use RefreshDatabase;

    private function applyingDivision(Handle ...$handles): Division
    {
        $division = Division::factory()->withHandles(...$handles)->create(['active' => true]);
        $division->settings()->set('application_required', true);
        $division->applicationFields()->delete();

        return $division->fresh();
    }

    private function applicant(): User
    {
        return User::factory()->pending()->create(['date_of_birth' => '2000-01-15', 'forum_password' => null]);
    }

    private function submit(User $user, Division $division, array $payload)
    {
        Notification::fake();

        return $this->actingAs($user)
            ->withSession(['pending_division_id' => $division->id])
            ->post(route('auth.discord.application'), $payload);
    }

    #[Test]
    public function a_single_handle_division_stores_the_handle_on_the_application(): void
    {
        $battlenet = Handle::factory()->create(['label' => 'Battle.net ID']);
        $division  = $this->applyingDivision($battlenet);
        $user      = $this->applicant();

        $this->submit($user, $division, ['handle_value' => ' Player#1234 '])->assertSessionHasNoErrors();

        $application = DivisionApplication::where('user_id', $user->id)->firstOrFail();
        $this->assertSame($battlenet->id, $application->handle_id);
        $this->assertSame('Player#1234', $application->handle_value);
    }

    #[Test]
    public function the_handle_is_required(): void
    {
        $division = $this->applyingDivision(Handle::factory()->create(['label' => 'Battle.net ID']));
        $user     = $this->applicant();

        $this->submit($user, $division, [])->assertSessionHasErrors('handle_value');

        $this->assertNull(DivisionApplication::where('user_id', $user->id)->first());
    }

    #[Test]
    public function a_multi_handle_division_requires_choosing_which_one(): void
    {
        $na       = Handle::factory()->create(['label' => 'Warships NA']);
        $eu       = Handle::factory()->create(['label' => 'Warships EU']);
        $division = $this->applyingDivision($na, $eu);
        $user     = $this->applicant();

        $this->submit($user, $division, ['handle_value' => '12345'])->assertSessionHasErrors('handle_id');

        $this->assertNull(DivisionApplication::where('user_id', $user->id)->first());
    }

    #[Test]
    public function the_value_is_checked_against_the_chosen_handles_format(): void
    {
        $na       = Handle::factory()->create(['label' => 'Warships NA']);
        $eu       = Handle::factory()->create(['label' => 'Warships EU', 'regex' => '/^\d+$/', 'regex_hint' => 'Must be numeric']);
        $division = $this->applyingDivision($na, $eu);
        $user     = $this->applicant();

        $this->submit($user, $division, ['handle_id' => $eu->id, 'handle_value' => 'NotANumber'])
            ->assertSessionHasErrors(['handle_value' => 'Must be numeric']);

        $this->submit($user, $division, ['handle_id' => $eu->id, 'handle_value' => '500123'])
            ->assertSessionHasNoErrors();

        $application = DivisionApplication::where('user_id', $user->id)->firstOrFail();
        $this->assertSame($eu->id, $application->handle_id);
        $this->assertSame('500123', $application->handle_value);
    }

    #[Test]
    public function a_handle_from_another_division_is_rejected(): void
    {
        $division = $this->applyingDivision(Handle::factory()->create(['label' => 'Warships NA']), Handle::factory()->create(['label' => 'Warships EU']));
        $other    = Handle::factory()->create(['label' => 'Origin']);
        $user     = $this->applicant();

        $this->submit($user, $division, ['handle_id' => $other->id, 'handle_value' => 'x'])->assertSessionHasErrors('handle_id');
    }

    #[Test]
    public function the_pending_page_lists_the_divisions_handles(): void
    {
        $na       = Handle::factory()->create(['label' => 'Warships NA', 'regex_hint' => 'Numeric player ID']);
        $eu       = Handle::factory()->create(['label' => 'Warships EU']);
        $division = $this->applyingDivision($na, $eu);
        $user     = $this->applicant();

        $this->actingAs($user)
            ->withSession(['pending_division_id' => $division->id])
            ->get(route('auth.discord.pending'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('auth/discord-pending')
                ->where('applicationHandles.0.label', 'Warships NA')
                ->where('applicationHandles.0.hint', 'Numeric player ID')
                ->where('applicationHandles.1.label', 'Warships EU'));
    }

    #[Test]
    public function recruiters_see_the_handle_on_the_application_and_pending_list(): void
    {
        $battlenet = Handle::factory()->create(['label' => 'Battle.net ID']);
        $division  = $this->applyingDivision($battlenet);
        $user      = $this->applicant();
        $this->submit($user, $division, ['handle_value' => 'Player#1234']);

        $application = DivisionApplication::where('user_id', $user->id)->firstOrFail();
        $officer     = $this->createOfficer();
        $expected    = ['id' => $battlenet->id, 'label' => 'Battle.net ID', 'value' => 'Player#1234'];

        $this->actingAs($officer)
            ->getJson(route('division-applications.show', [$division->slug, $application->id]))
            ->assertOk()
            ->assertJsonPath('application.handle', $expected);

        $this->actingAs($officer)
            ->getJson(route('recruiting.pendingDiscord', $division->slug))
            ->assertOk()
            ->assertJsonPath('pending_discord.0.application_handle', $expected);
    }
}
